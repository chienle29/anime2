<?php

namespace CTMovie\Model;

use Goutte\Client;
use WP_Post;
use DOMElement;
use Exception;
use Illuminate\Support\Str;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use CTMovie\Objects\Cache\ResponseCache;
use CTMovie\Utils;
use Symfony\Component\HttpClient\HttpClient;
use CTMovie\Model\Media\MediaService;
use CTMovie\Model\Media\MediaSavingOptions;
use CTMovie\Model\Media\MediaFile;

/**
 * Class AbstractBot
 * @package CTMovie\Model
 */
abstract class AbstractBot
{
    private $selectAllRegex = '^.*$';

    /**
     * @var Client
     */
    protected $client;

    /** @var bool */
    private $useUtf8;

    /** @var bool */
    private $convertEncodingToUtf8;

    /** @var bool */
    private $allowCookies;

    /** @var string */
    private $httpAccept;

    /** @var string */
    private $httpUserAgent;

    /** @var int */
    private $connectionTimeout;

    /** @var array */
    public $preparedProxyList = [];

    /** @var int */
    private $siteId;

    /** @var WP_Post The site (WP Content Crawler site) which is being crawled */
    private $site;

    /** @var string Stores the content of the latest response */
    private $latestResponseContent;

    /** @var bool Stores whether the last response has been retrieved from cache or not. */
    private $isLatestResponseFromCache = false;

    /** @var bool */
    private $isResponseCacheEnabled = false;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var Response|null Stores the response of the latest request. If the response was retrieved from the cache, this
     *      will be null.
     */
    private $latestResponse = null;

    /**
     * @var Exception|null Exception thrown for the latest request. If no exception is thrown for the latest request,
     *      this is null.
     */
    private $latestRequestException = null;

    /**
     * @param array $settings Settings for the site to be crawled
     * @param null|int $siteId ID of the site.
     * @param null|bool $useUtf8 If null, settings will be used to decide whether utf8 should be used or
     *                                         not. If bool, it will be used directly without considering settings. In
     *                                         other words, bool overrides the settings.
     * @param null|bool $convertEncodingToUtf8 True if encoding of the response should be converted to UTF8 when there
     *                                         is a different encoding. If null, settings will be used to decide. If
     *                                         bool, it will be used directly without considering settings. In other
     *                                         words, bool overrides the settings. This is applicable only if $useUtf8
     *                                         is found as true.
     */
    public function __construct($settings, $siteId = null)
    {
        if ($siteId) $this->siteId = $siteId;

        $this->settings = $settings;

        // Set client settings by using user's preferences.
        $this->allowCookies = true;

        // Set ACCEPT and USER_AGENT. If these settings do not exist, use default values.
        $this->httpAccept = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        $this->httpUserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';

        $this->connectionTimeout = 0;

        $this->createClient();
    }

    /**
     * @return Crawler|null
     * @since 1.11.0
     */
    public abstract function getCrawler();

    /**
     * @param Crawler|null
     * @since 1.11.0
     */
    public abstract function setCrawler($crawler): void;

    /**
     * Creates a client to be used to perform browser actions
     *
     * @param null|string $proxyUrl Proxy URL
     * @param null|string $protocol "http" or "https"
     */
    public function createClient($proxyUrl = null, $protocol = "http")
    {
        $config = [];

        if ($this->connectionTimeout) {
            $config['connect_timeout'] = $this->connectionTimeout;
            $config['timeout'] = $this->connectionTimeout;
        }

        // Set the proxy
        if ($proxyUrl) {
            if (!$protocol) $protocol = "http";

            if (in_array($protocol, ["http", "https", "tcp"])) {
                $config['proxy'] = [
                    $protocol => $proxyUrl
                ];
            }
        }

        $this->client = new Client(HttpClient::create($config));

        if ($this->httpAccept) $this->client->setServerParameter("HTTP_ACCEPT", $this->httpAccept);
        if ($this->httpUserAgent) $this->client->setServerParameter('HTTP_USER_AGENT', $this->httpUserAgent);

        /**
         * Modify the client that will be used to make requests.
         *
         * @param Client client The client
         * @param AbstractBot $this The bot itself
         *
         * @return Client Modified client
         * @since 1.6.3
         */
        $this->client = apply_filters('tc/bot/client', $this->client, $this);
    }

    /**
     * Creates a new Client and prepares it by adding Accept and User-Agent headers and enabling cookies.
     * Some other routines can also be done here.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $url Target URL
     * @param string $method Request method
     *                                      value, see {@see FindAndReplaceTrait::findAndReplace}. Default: null
     * @return Crawler|null
     */
    public function request($url, $method = "GET")
    {
        $proxyList = $this->preparedProxyList;
        $protocol = Str::startsWith($url, "https") ? "https" : "http";
        $proxyUrl = $proxyList && isset($proxyList[0]) ? $proxyList[0] : false;
        $tryCount = 0;

        do {
            try {
                // Make the request and get the response text. If the method succeeded, the response text will be
                // available in $this->latestResponseContent
                $responseText = $this->getResponseText($method, $url, $proxyUrl, $protocol);
                if (!$responseText) return null;

                // Assign it as the latest response content
                $this->latestResponseContent = $responseText;

                /**
                 * Modify the response content.
                 *
                 * @param string $latestResponseContent Response content after the previously-set find-and-replace settings are applied
                 * @param string $url The URL that sent the response
                 * @param AbstractBot $this The bot itself
                 *
                 * @return string Modified response content
                 * @since 1.7.1
                 */
                $this->latestResponseContent = apply_filters('tc/bot/response-content', $this->latestResponseContent, $url, $this);

                // Try to get the HTML content. If this causes an error, we'll catch it and return null.
                $crawler = $this->createCrawler($this->latestResponseContent, $url);

                // Try to get the HTML from the crawler to see if it can do it. Otherwise, it will throw an
                // InvalidArgumentException, which we will catch.
                $crawler->html();

                return $crawler;

            } catch (ConnectException $e) {
                // If the URL cannot be fetched, try another proxy, if exists.
                $this->latestRequestException = $e;
                $tryCount++;

                // Get the next proxy
                $proxyUrl = $proxyList[$tryCount];

            } catch (RequestException $e) {
                // If the URL cannot be fetched, then just return null.
                $this->latestRequestException = $e;

                break;

            } catch (InvalidArgumentException $e) {
                // If the HTML could not be retrieved, then just return null.
                $this->latestRequestException = $e;
                break;

            } catch (Exception $e) {
                // If there is an error, return null.
                $this->latestRequestException = $e;
                break;
            }

        } while (true);

        return null;
    }

    /**
     * Enable/disable response caching
     *
     * @param true $enabled Enable or disable the response cache. True to enable.
     * @since 1.8.0
     */
    public function setResponseCacheEnabled($enabled)
    {
        $this->isResponseCacheEnabled = $enabled;

    }

    /**
     * @return bool
     */
    public function isLatestResponseFromCache()
    {
        return $this->isLatestResponseFromCache;
    }

    /**
     * Makes a request to the given URL with the given method considering the cookies and using given proxy. Then,
     * returns the response text.
     *
     * @param string $method HTTP request method, e.g. GET, POST, HEAD, PUT, DELETE
     * @param string $url Target URL
     * @param string|null $proxyUrl See {@link createClient()}
     * @param string|null $protocol See {@link createClient()}
     * @return false|string
     * @since 1.8.0
     */
    protected function getResponseText($method, $url, $proxyUrl, $protocol)
    {
        $this->latestResponse = null;
        $this->latestRequestException = null;

        // If caching is enabled, try to get the response from cache.
        $this->isLatestResponseFromCache = false;
        if ($this->isResponseCacheEnabled) {
            $response = ResponseCache::getInstance()->get($method, $url);
            if ($response) {
                $this->isLatestResponseFromCache = true;
                return $response;
            }
        }

        // If there is a proxy, create a new client with the proxy settings.
        if ($proxyUrl) $this->createClient($proxyUrl, $protocol);

        $this->getClient()->request($method, $url);

        // Get the response and its HTTP status code
        $this->latestResponse = $this->getClient()->getInternalResponse();

        $status = $this->latestResponse->getStatusCode();

        switch ($status) {
            // Do not proceed if the target URL is not found.
            case 404:
                return false;
        }

        // Do not proceed if there was a server error.
        if ($status >= 500 && $status < 600) {
            return false;
        }

        $content = $this->latestResponse->getContent();

        // If caching enabled, cache the response.
        if ($this->isResponseCacheEnabled) ResponseCache::getInstance()->save($method, $url, $content);

        // Return the content of the response
        return $content;
    }

    /**
     * Modify a node with a callback.
     *
     * @param Crawler $crawler The crawler in which the elements will be searched for
     * @param array|string $selectors Selectors to be used to find the elements.
     * @param callable $callback A callback that takes only one argument, which is the found node, e.g.
     *                                function(Crawler $node) {}
     */
    public function modifyElementWithCallback(&$crawler, $selectors, $callback)
    {
        if (empty($selectors) || !$crawler || !is_callable($callback)) return;

        if (!is_array($selectors)) $selectors = [$selectors];

        foreach ($selectors as $selector) {
            if (!$selector) continue;

            try {
                $crawler->filter($selector)->each(function ($node, $i) use (&$callback) {
                    /** @var Crawler $node */
                    call_user_func($callback, $node);
                });

            } catch (Exception $e) {

            }
        }
    }

    /**
     * Remove an attribute of the elements found via selectors.
     *
     * @param Crawler $crawler
     * @param array|string $selectors
     * @param string $attrName Name of the attribute. E.g. "src". You can set more than one attribute by writing
     *                               the attributes comma-separated. E.g. "src,data-src,width,height"
     */
    public function removeElementAttributes(&$crawler, $selectors, $attrName)
    {
        if (empty($selectors) || !$attrName || !$crawler) return;

        if (!is_array($selectors)) $selectors = [$selectors];

        // Prepare the attribute names
        $attrNames = array_map(function ($name) {
            return trim($name);
        }, array_filter(explode(",", $attrName)));

        foreach ($selectors as $selector) {
            if (!$selector) continue;

            try {
                $crawler->filter($selector)->each(function ($node, $i) use (&$attrNames) {
                    /** @var Crawler $node */
                    /** @var DOMElement $child */
                    $child = $node->getNode(0);

                    // Remove the attribute
                    foreach ($attrNames as $attrName) $child->removeAttribute($attrName);
                });

            } catch (Exception $e) {

            }
        }
    }

    /**
     * Extracts specified data from the crawler
     *
     * @param Crawler $crawler
     * @param array|string $selectors A single selector as string or more than one selectors as array
     * @param string|array $dataType "text", "html", "href" or attribute of the element (e.g. "content")
     * @param string|null|false $contentType Type of found content. This will be included as "type" in resultant
     *                                        array.
     * @param bool $singleResult True if you want a single result, false if you want all matches. If true,
     *                                        the first match will be returned.
     * @param bool $trim True if you want each match trimmed, false otherwise.
     * @return array|null|string              If found, the result. Otherwise, null. If there is a valid content
     *                                        type, then the result will include an array including the position of
     *                                        the found value in the crawler HTML. If the content type is null or
     *                                        false, then just the found value will be included. <p><p> If there are
     *                                        more than one dataType:
     *                                        <li>If more than one match is found, then the "data" value will be an
     *                                        array.</li>
     *                                        <li>If only one match is found, then the data will be a string.</li>
     */
    public function extractData($crawler, $selectors, $dataType, $contentType, $singleResult, $trim)
    {
        // Check if the selectors are empty. If so, do not bother.
        if (empty($selectors) || !$crawler) return null;

        // If the selectors is not an array, make it one.
        if (!is_array($selectors)) $selectors = [$selectors];

        // If the data type is not an array, make it one.
        if (!is_array($dataType)) {
            $dataType = [$dataType];
        } else {
            // Make sure each type in the data type array is unique
            $dataType = array_unique($dataType);
        }

        $crawlerHtml = $crawler->html();
        $results = [];

        foreach ($selectors as $selector) {
            if (!$selector) continue;
            if ($singleResult && !empty($results)) break;

            $offset = 0;
            try {
                $crawler->filter($selector)->each(function ($node, $i) use (
                    $crawler, $dataType,
                    $singleResult, $trim, $contentType, &$results, &$offset, &$crawlerHtml
                ) {
                    /** @var Crawler $node */

                    // If single result is needed and we have found one, then do not continue.
                    if ($singleResult && !empty($results)) return;

                    $value = null;
                    foreach ($dataType as $dt) {
                        try {
                            $val = null;
                            switch ($dt) {
                                case "text":
                                    $val = $node->text();
                                    break;
                                case "html":
                                    $val = Utils::getNodeHTML($node);
                                    break;
                                default:
                                    $val = $node->attr($dt);
                                    break;
                            }

                            if ($val) {
                                if ($trim) $val = trim($val);
                                if ($val) {
                                    if (!$value) $value = [];
                                    $value[$dt] = $val;
                                }
                            }

                        } catch (InvalidArgumentException $e) {
                        }
                    }

                    try {
                        if ($value && !empty($value)) {
                            if ($contentType) {
                                $html = Utils::getNodeHTML($node);
                                $start = mb_strpos($crawlerHtml, $html, $offset);
                                $results[] = [
                                    "type" => $contentType,
                                    "data" => sizeof($value) == 1 ? array_values($value)[0] : $value,
                                    "start" => $start,
                                    "end" => $start + mb_strlen($html)
                                ];
                                $offset = $start + 1;
                            } else {
                                $results[] = sizeof($value) == 1 ? array_values($value)[0] : $value;
                            }
                        }

                    } catch (InvalidArgumentException $e) {
                    }
                });

            } catch (Exception $e) {

            }
        }

        // Return the results
        if ($singleResult && !empty($results)) {
            return $results[0];
        } else if (!empty($results)) {
            return $results;
        }

        return null;
    }

    /**
     * Creates a crawler with the right encoding.
     *
     * @param string $html
     * @param string $url
     * @return Crawler
     */
    public function createCrawler($html, $url)
    {
        if ($this->useUtf8) {
            // Check if charset is defined as meta Content-Type. If so, replace it.
            // The regex below is taken from Symfony\Component\DomCrawler\Crawler::addContent
            $regexCharset = '/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9_:.]+)/i';
            if (preg_match($regexCharset, $html, $matches)) {
                // Change only if it is not already utf-8
                $charset = $matches[1];
                if (strtolower($charset) !== "utf-8") {

                    // Convert the encoding from the defined charset to UTF-8 if it is required
                    if ($this->convertEncodingToUtf8) {
                        // Get available encodings
                        $availableEncodings = array_map('strtolower', mb_list_encodings());

                        // Make sure the encoding exists in available encodings.
                        if (in_array(strtolower($charset), $availableEncodings)) {
                            $html = mb_convert_encoding($html, "UTF-8", $charset);

                            // Now match again to get the right positions after converting the encoding. I'm not sure if the
                            // positions might change after converting the encoding. Hence, to be on the safe side, we're
                            // matching again.
                            preg_match($regexCharset, $html, $matches);

                            // Otherwise, we cannot convert the encoding. Inform the user.
                        } else {

                        }
                    }

                    if ($matches) {
                        $pos0 = stripos($html, $matches[0]);
                        $pos1 = $pos0 + stripos($matches[0], $matches[1]);

                        $html = substr_replace($html, "UTF-8", $pos1, strlen($matches[1]));
                    }
                }

                // Otherwise
            } else {
                // Make sure the charset is UTF-8
                $html = '';
            }
        }

        // Remove chars that come before the first "<"
        $html = mb_substr($html, mb_strpos($html, "<"));

        // Remove chars that come after the last ">"
        $html = mb_substr($html, 0, mb_strrpos($html, ">") + 1);

        $crawler = new Crawler(null, $url);
        $crawler->addContent($html);

        return $crawler;
    }

    /**
     * Creates a dummy Crawler from an HTML.
     *
     * @param string $html
     * @return Crawler
     */
    public function createDummyCrawler($html)
    {
        $html = "<html><head><meta charset='utf-8'></head><body><div>" . $html . "</div></body></html>";
        return new Crawler($html);
    }

    /**
     * @return int|null Site ID for which this bot is created
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @return WP_Post|null See {@link $site}
     * @since 1.11.0
     */
    public function getSite(): ?WP_Post
    {
        $this->loadSiteIfPossible();
        return $this->site;
    }

    /**
     * @return string See {@link $latestResponseContent}
     */
    public function getLatestResponseContent()
    {
        return $this->latestResponseContent;
    }

    /**
     * @return Response|null See {@link $latestResponse}
     * @since 1.11.0
     */
    public function getLatestResponse(): ?Response
    {
        return $this->latestResponse;
    }

    /**
     * @return Exception|null See {@link $latestRequestException}
     * @since 1.11.0
     */
    public function getLatestRequestException(): ?Exception
    {
        return $this->latestRequestException;
    }


    /**
     * Sets {@link $site} variable if there is a valid {@link $siteId}.
     */
    private function loadSiteIfPossible()
    {
        if (!$this->site && $this->siteId) {
            $this->site = get_post($this->siteId);
        }
    }

    /**
     * Get values for a selector setting. This applies the options box configurations as well.
     *
     * @param Crawler $crawler See {@link AbstractBot::extractValuesWithSelectorData}
     * @param string $selectors Name of the setting from which the selector data will be retrieved
     * @param string $defaultAttr See {@link AbstractBot::extractValuesWithSelectorData}
     * @param bool $contentType See {@link AbstractBot::extractData}
     * @param bool $singleResult See {@link AbstractBot::extractData}
     * @param bool $trim See {@link AbstractBot::extractData}
     * @return array|mixed|null      If there are no results, returns null. If $singleResult is true, returns a single
     *                               result. Otherwise, returns an array. If $singleResult is false, returns an array
     *                               of arrays, where each inner array is the result of a single selector data.
     */
    public function extractValuesForSelectorSetting($crawler, $selectors, $attr, $defaultAttr, $contentType = false, $singleResult = false, $trim = true)
    {
        if (!$selectors) return null;

        $data = ['selector' => $selectors, 'attribute' => $attr];
        $result = $this->extractValuesWithSelectorData($crawler, $data, $defaultAttr, $contentType, $singleResult, $trim);

        if (!$result) return null;
        return $result;
    }

    /**
     * Extract values from the crawler using selector data.
     *
     * @param Crawler $crawler The crawler from which the data should be extracted
     * @param array $data Selector data that have these keys: "selector" (optional), "attr" (optional),
     *                              "options_box" (optional).
     * @param string $defaultAttr Attribute value that will be used if the attribute is not found in the settings
     * @param bool $contentType See {@link AbstractBot::extractData}
     * @param bool $singleResult See {@link AbstractBot::extractData}
     * @param bool $trim See {@link AbstractBot::extractData}
     * @return array|null|string See {@link AbstractBot::extractData}
     * @since 1.8.0
     */
    public function extractValuesWithSelectorData($crawler, $data, $defaultAttr, $contentType = false, $singleResult = false, $trim = true)
    {
        $selector = $data['selector'];
        $attr = $data['attribute'];
        if (!$attr) $attr = $defaultAttr;

        $result = $this->extractData($crawler, $selector, $attr, $contentType, $singleResult, $trim);
        if (!$result) return null;

        return $result;
    }

    /**
     * Save file to post
     * @param $url
     * @param $timeout
     * @param $postId
     */
    public function saveFile($url, $timeout, $postId)
    {
        include_once(trailingslashit(ABSPATH) . 'wp-admin/includes/file.php');
        include_once(trailingslashit(ABSPATH) . 'wp-includes/pluggable.php');
        $file = MediaService::getInstance()
            ->saveMedia($url, MediaSavingOptions::fromSiteSettings($timeout));
        if (!$file) return;

        $mediaFile = new MediaFile($url, $file['file']);
        // If there is no file, stop.
        if (!$mediaFile) return;

        // Save as attachment and get the attachment id.
        try {
            $thumbnailAttachmentId = MediaService::getInstance()->insertMedia($postId, $mediaFile);
        } catch (Exception $e) {
            //Media file does not have a local path.
            return;
        }

        // Set the media ID
        $mediaFile->setMediaId($thumbnailAttachmentId);

        // Set this attachment as post thumbnail
        set_post_thumbnail($postId, $thumbnailAttachmentId);
    }
}