<?php

namespace CTMovie\Model\Crawling;

use CTMovie\Model\AbstractBot;
use CTMovie\Utils;
use CTMovie\Model\Settings;
use CTMovie\Model\Crawling\Data\MovieUrlList;
use CTMovie\Model\Crawling\Data\MovieUrl;
use CTMovie\Api\MakesCrawlRequest;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DomCrawler\Crawler;
use CTMovie\Model\Crawling\Data\CategoryData;
use Illuminate\Support\Arr;

/**
 * Class CategoryBot
 * @package CTMovie\Model\Crawling
 */
class CategoryBot extends AbstractBot implements MakesCrawlRequest
{
    /** @var Crawler */
    private $crawler;

    /** @var string */
    private $url;

    /** @var int|null HTTP status code of the response of the crawling request */
    private $responseHttpStatusCode = null;

    private $categoryData;

    /** @var null|Uri */
    private $uri = null;

    /**
     * @return string|null Returns {@link url}, last crawled or being crawled category URL.
     * @since 1.8.0
     * @since 1.11.0 Renamed from getUrl to getCrawlingUrl
     */
    public function getCrawlingUrl(): ?string
    {
        return $this->url;
    }

    public function getResponseHttpStatusCode(): ?int
    {
        return $this->responseHttpStatusCode;
    }

    public function getCrawler()
    {
        return $this->crawler;
    }

    public function setCrawler($crawler): void
    {
        $this->crawler = $crawler;
    }

    /**
     * Collects URLs for a site from the given URL
     *
     * @param string $url A full URL to be used to get post URLs
     * @return array|null
     */
    public function collectUrls($url): ?CategoryData
    {
        $this->categoryData = new CategoryData();
        $this->setUrl($url);
        try {
            $this->crawler = $this->request($this->url, "GET");

            $this->responseHttpStatusCode = $this->getLatestResponse() ? $this->getLatestResponse()->getStatusCode() : null;

            if (!$this->crawler) return null;

            $this->preparePostUrls()->prepareNextPageUrl();

        } catch (\Exception $e) {
            return null;
        }

        return $this->getCategoryData();
    }

    /**
     * Set current category URL
     *
     * @param string $url
     * @since 1.8.0
     */
    private function setUrl($url)
    {
        $this->url = $url;
        $this->uri = null;
    }

    /**
     * @return CategoryData See {@link categoryData}
     * @since 1.11.0
     */
    public function getCategoryData(): CategoryData
    {
        return $this->categoryData;
    }

    /**
     * Prepare post URLs
     * @return CategoryBot
     */
    private function preparePostUrls()
    {
        $postUrlData = $this->extractValuesForSelectorSetting(
            $this->crawler,
            $this->settings[Settings::MOVIE_URL_IN_CATE_SELECTOR],
            'href',
            'url',
            false,
            false
        );
        if (!$postUrlData) return $this;

        // Flatten the array with depth of 1 because the return value is array of arrays of items. We need array of items.
        $postUrlData = Arr::flatten($postUrlData, 1);

        // Make relative URLs direct
        $urlList = new MovieUrlList();
        foreach ($postUrlData as $mPostUrl) {
            $urlItem = MovieUrl::fromArray($mPostUrl);
            if (!$urlItem) continue;

            $urlList->addItem($urlItem);

            try {
                $urlItem->setUrl($this->resolveUrl($urlItem->getUrl()));

            } catch (\Exception $e) {

            }
        }

        $this->categoryData->setPostUrlList($urlList);
        return $this;
    }

    /**
     * Resolves a URL by considering {@link url} as base URL.
     *
     * @param string $relativeUrl Relative or full URL that will be resolved against the current category URL.
     * @return string The given URL that is resolved using {@link url}
     * @throws \Exception If category URL that will be used to resolve the given URL does not exist.
     * @since 1.8.0
     * @see   Utils::resolveUrl()
     */
    public function resolveUrl($relativeUrl)
    {
        if (!$this->url) {
            throw new \Exception("Category URL does not exist.");
        }

        // If there is no post URI, create it.
        if ($this->uri === null) {
            $this->uri = new Uri($this->url);
        }

        return Utils::resolveUrl($this->uri, $relativeUrl);
    }

    /**
     * Prepare next page URL
     * @return $this
     */
    private function prepareNextPageUrl()
    {
        if (empty($this->settings[Settings::CATEGORY_LAST_CHECKED_URL][0])) {
            $nextPageUrl = '?aph=&page=2';
        } else {
            $targetPage = str_replace($this->settings[Settings::CATEGORY_MAP][0] . '?aph=&page=', '', $this->settings[Settings::CATEGORY_LAST_CHECKED_URL][0]);
            $nextPageUrl = '?aph=&page=' . ($targetPage + 1);
            if ($targetPage >= 81) $nextPageUrl = null;
        }
        if (!$nextPageUrl) {
            $this->categoryData->setNextPageUrl($nextPageUrl);
            return $this;
        }

        try {
            $nextPageUrl = $this->resolveUrl($nextPageUrl);
        } catch (\Exception $e) {

        }

        $this->categoryData->setNextPageUrl($nextPageUrl);
        return $this;
    }
}