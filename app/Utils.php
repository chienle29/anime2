<?php

namespace CTMovie;

use CTMovie\Model\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Philo\Blade\Blade;
use Symfony\Component\DomCrawler\Crawler;
use WP_Post;
use CTMovie\Environment;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;

/**
 * Class Utils
 * @package CTMovie
 */
class Utils
{
    /** @var array An associative array storing site ID (the custom post type) as key and site name as value. */
    private static $campaigns = null;

    /**
     * @var Blade
     */
    private static $BLADE;

    /**
     * Create a blade view that can be rendered.
     * @param string $viewName
     * @return View
     */
    public static function view(string $viewName)
    {
        if (!static::$BLADE) {
            $views = __DIR__ . Environment::CT_RELATIVE_VIEWS_DIR;
            $cache = __DIR__ . Environment::CT_RELATIVE_CACHE_DIR;

            static::$BLADE = new Blade($views, $cache);
        }

        return static::$BLADE->view()->make($viewName);
    }

    /**
     * Get categories as an array.
     * @return array Structure: [ ['id' => 'categoryId', 'name' => 'Category Name', 'taxonomy' => 'categoryTaxonomy'], ...]
     */
    public static function getCategories()
    {
        // Prepare the categories
        $categories = [];
        // Get the categories
        $cats = get_categories([
            'taxonomy' => 'category',
            'orderby' => 'name',
            'hierarchical' => 0,
            'hide_empty' => false,
        ]);

        // Prepare them
        foreach ($cats as $cat) {
            $name = $cat->name;

            // Store it
            $categories[] = [
                'id' => $cat->cat_ID,
                'name' => $name . " ({$cat->cat_ID})", // Add the category ID
            ];
        }

        return $categories;
    }

    /**
     * Saves or updates a post meta for a post. <b>Note that</b> the meta key will be prefixed with an underscore if
     * it does not start with it. The meta keys starting with an underscore will be hidden on post edit/create page.
     * Hence, the meta keys can be shown with custom meta boxes.
     *
     * @param int $postId
     * @param mixed $metaKey
     * @param mixed $metaValue
     * @param bool $unique
     * @return bool|false|int
     */
    public static function savePostMeta($postId, $metaKey, $metaValue, $unique = true) {
        if ($unique) {
            return update_post_meta($postId, $metaKey, $metaValue);
        } else {
            return add_post_meta($postId, $metaKey, $metaValue, false);
        }
    }

    /**
     * Get campaigns in a structure that can be used to easily show them in a select element.
     *
     * @return array A key-value pair where the keys are site IDs and the values are the site names.
     * @uses Utils::getSites()
     * @since 1.9.0
     */
    public static function getCampaignsForSelect() {
        // Get available campaigns
        $availableCampaigns = Utils::getCampagins();
        if (!$availableCampaigns) return [];

        $campaigns = [];
        foreach($availableCampaigns as $campaign) {
            $campaigns[$campaign->ID] = $campaign->post_title;
        }

        return $campaigns;
    }

    /**
     * Get published campaigns.
     *
     * @return array See {@link get_posts}.
     * @uses get_posts
     * @since 1.9.0
     */
    public static function getCampagins() {
        // If the value was prepared before, return it.
        if (static::$campaigns) return static::$campaigns;

        // Get the sites
        $allCampaigns = get_posts(['post_type' => Environment::CT_POST_TYPE, 'numberposts' => -1]);

        // Define a default title for the sites that do not have a title.
        $defaultTitle = __('(no title)');

        // Prepare the sites
        array_walk($allCampaigns, function($item) use (&$defaultTitle) {
            /** @var WP_Post $item */

            // If the site does not have a title, set its title as the default title.
            if (!$item->post_title) $item->post_title = $defaultTitle;

            // Add the ID to the title.
            $item->post_title .= " ({$item->ID})";
        });

        static::$campaigns = $allCampaigns;

        return static::$campaigns;
    }

    /**
     * Gets the HTML of the specified element with its own tag
     * @param Crawler $node
     * @return string HTML of the element
     */
    public static function getNodeHTML($node) {
        if(!$node || !$node->getNode(0)) return '';
        return $node->getNode(0)->ownerDocument->saveHTML($node->getNode(0));
    }

    /**
     * Prepares a valid URL from given parameters.
     *
     * @param string      $baseUrl
     * @param string      $urlPartToAppend
     * @param null|string $currentUrl Current page's URL. If this is null, $baseUrl will be used instead.
     * @return null|string A valid URL created from the givens
     */
    public static function prepareUrl($baseUrl, $urlPartToAppend, $currentUrl = null) {
        // If the URL starts with double slashes ("//"), prepend "http:" and return.
        if(substr($urlPartToAppend, 0, 2) == '//') {
            return "http:" . $urlPartToAppend;
        }

        // Remove the trailing slash from the base url
        $baseUrl = rtrim($baseUrl, "/");

        // If the url does not start with http, add main site url in front of it
        if(!Str::startsWith($urlPartToAppend, "http")) {
            // If URL part starts with "www", just add "http://" in front of it and return.
            if(Str::startsWith($urlPartToAppend, "www")) return "http://" . $urlPartToAppend;

            // Remove the first leading slash from the url, if exists.
            if(Str::startsWith($urlPartToAppend, "/")) {
                $urlPartToAppend = substr($urlPartToAppend, 1);

                // If not, prepend current URL.
            } else {
                // The URL part is like "other/page.html". Let's say the current URL is "http://site.com/my/page". In
                // this case, browsers consider "other/page.html" link as "http://site.com/my/other/page.html". Here,
                // we are handling this situation.

                $currentUrl = $currentUrl ? $currentUrl : $baseUrl;

                // If the current URL does not end with a forward slash and the URL part to append does not start with
                // a question mark, we need to get the base resource URL.
                if(!Str::endsWith($currentUrl, "/") && !Str::startsWith($urlPartToAppend, "?")) {
                    // Remove the last part from the URL when the URL has more than one resource.
                    // First, remove the part until ://. Then, explode it from forward slashes.
                    $parts = explode("/", preg_replace("%^[^:]+://%", "", $currentUrl));
                    if (sizeof($parts) > 1) {
                        $currentUrl = pathinfo($currentUrl, PATHINFO_DIRNAME);
                    }

                } /** @noinspection PhpStatementHasEmptyBodyInspection */ else {
                    // When the URL ends with a forward slash, or the URL to append starts with a question mark, it
                    // means the URL currently points to the URL relative to this url part. E.g. when the URL is
                    // "http://abc.com/test/page/", and url part to append is "page.html", it means the intended URL is
                    // "http://abc.com/test/page/page.html". Or, when the URL is "http://abc.com/test/page" and url part
                    // to append is "?num=2", the intended URL is "http://abc.com/test/page?num=2"
                    // So, nothing to do here.
                }

                $currentUrl = rtrim($currentUrl, "/");
                if(!Str::startsWith($urlPartToAppend, "?")) $currentUrl .= "/";

                return $currentUrl . $urlPartToAppend;
            }

            // Prepare the full url and return it.
            return $baseUrl . "/" . $urlPartToAppend;
        }

        return $urlPartToAppend;
    }

    /**
     * Resolves a URL.
     *
     * @param Uri    $baseUri
     * @param string $relativeUrl Relative or full URL that will be resolved against the given {@link Uri}.
     * @since 1.8.0
     * @return string
     */
    public static function resolveUrl($baseUri, $relativeUrl) {
        try {
            // Try to resolve the relative URL
            $resolvedUri = UriResolver::resolve($baseUri, new Uri($relativeUrl));

            // Return the resolved URI
            return $resolvedUri->__toString();

        } catch(\Exception $e) {
            return $relativeUrl;
        }
    }

    /**
     * Strips slashes of non-array values of the array.
     *
     * @param array $array The array whose string values' slashes will be stripped
     * @return array The array with slashes of its string values are stripped
     */
    public static function arrayStripSlashes($array) {
        $mArray = [];
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $mArray[$key] = static::arrayStripSlashes($value);
            } else {
                $mArray[$key] = stripslashes($value);
            }
        }

        return $mArray;
    }

    /**
     * Get a value from an array
     *
     * @param array $array      The array
     * @param string $key       The key whose value is wanted
     * @param mixed $default    Default value if the value of the key is not valid
     * @return mixed            Value of the key or the default value
     */
    public static function getValueFromArray($array, $key, $default = false) {
        return isset($array[$key]) && $array[$key] ? $array[$key] : $default;
    }

    /**
     * @return array
     */
    public static function getOptionKeys()
    {
        return [
            Settings::AUTO_CRAWL_MOVIE,
            Settings::COLLECT_URLS_INTERVAL,
            Settings::CREATE_SERIES_INTERVAL,
            Settings::CRAWL_ANIME_INTERVAL,
            Settings::LAU_API_KEY
        ];
    }
}