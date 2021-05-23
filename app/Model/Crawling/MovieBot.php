<?php

namespace CTMovie\Model\Crawling;

use CTMovie\Api\MakesCrawlRequest;
use CTMovie\Model\AbstractBot;
use CTMovie\Model\Settings;
use CTMovie\ObjectFactory;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DomCrawler\Crawler;
use CTMovie\Model\Crawling\Data\PostData;
use CTMovie\Utils;
use CTMovie\Model\AbstractPostBotPreparer;
use CTMovie\Model\Crawling\Data\MovieTitlePreparer;
use CTMovie\Model\Crawling\Data\MovieContentsPreparer;
use CTMovie\Model\Crawling\Data\MovieThumbnailPreparer;
use CTMovie\Model\Crawling\Data\MovieStatusPreparer;
use CTMovie\Model\Crawling\Data\MovieReleasedPreparer;
use CTMovie\Model\Crawling\Data\MovieGenrePreparer;
use CTMovie\Model\Crawling\Data\MovieChapterUrlPreparer;
use CTMovie\Model\Crawling\Data\MovieEpisodePrepare;
use CTMovie\Model\Crawling\Data\EpisodeDownloadsPrepare;
use CTMovie\Model\Crawling\Data\EpisodeUrlDownloadList;
use CTMovie\Model\Crawling\Data\EpisodeNameDownloadList;

use CTMovie\Model\DatabaseService;

class MovieBot extends AbstractBot implements MakesCrawlRequest
{
    /** @var Crawler */
    private $crawler;

    /** @var PostData */
    private $postData;

    /** @var string */
    private $postUrl = '';

    /** @var int|null HTTP status code of the response of the crawling request */
    private $responseHttpStatusCode = null;

    /** @var null|Uri */
    private $postUri = null;

    /**
     * Crawls a post and prepares the data as {@link PostData}. This method does not save the post to the database.
     *
     * @param string|null        $postUrl       A full URL
     * @return PostData|null
     * @throws \Exception See {@link triggerEvent()}
     */
    public function crawlPost(?string $postUrl): ?PostData
    {
        if (!$postUrl) return null;

        $this->setPostUrl($postUrl);
        $this->postData = new PostData();

        $this->crawler = $this->request($postUrl, "GET");

        $this->responseHttpStatusCode = $this->getLatestResponse() ? $this->getLatestResponse()->getStatusCode() : null;

        if(!$this->crawler) return null;
        // Get movie title, description, thumbnail url, status, released.
        $this->applyPreparer(MovieTitlePreparer::class, $this->settings[Settings::MOVIE_TITLE_SELECTOR][0], $this->settings[Settings::MOVIE_TITLE_SELECTOR_ATTR][0]);
        $this->applyPreparer(MovieContentsPreparer::class, $this->settings[Settings::MOVIE_DESCRIPTION_SELECTOR][0], $this->settings[Settings::MOVIE_DESCRIPTION_SELECTOR_ATTR][0]);
        $this->applyPreparer(MovieThumbnailPreparer::class, $this->settings[Settings::THUMBNAIL_SELECTOR][0], $this->settings[Settings::THUMBNAIL_SELECTOR_ATTR][0]);
        $this->applyPreparer(MovieStatusPreparer::class, $this->settings[Settings::MOVIE_STATUS_SELECTOR][0], $this->settings[Settings::MOVIE_STATUS_SELECTOR_ATTR][0]);
        $this->applyPreparer(MovieReleasedPreparer::class, $this->settings[Settings::MOVIE_RELEASED_SELECTOR][0], $this->settings[Settings::MOVIE_RELEASED_SELECTOR_ATTR][0]);
        // Đang set cứng, sẽ thêm ở config sau.(chưa tạo đc taxonomy genre).
        //$this->applyPreparer(MovieGenrePreparer::class, '.anime_info_body p:nth-child(6) a', 'text');
        $this->applyPreparer(MovieEpisodePrepare::class, $this->settings[Settings::MOVIE_EPISODE][0], $this->settings[Settings::MOVIE_EPISODE_ATTR][0]);
        return $this->postData;
    }

    /**
     * get info of episode
     * @param string|null $postUrl
     * @return PostData|null
     * @throws \Exception
     */
    public function crawlEpisode(?string $postUrl) {
        if (!$postUrl) return null;

        $this->setPostUrl($postUrl);
        $this->postData = new PostData();

        $this->crawler = $this->request($postUrl, "GET");

        $this->responseHttpStatusCode = $this->getLatestResponse() ? $this->getLatestResponse()->getStatusCode() : null;

        if(!$this->crawler) return null;
        // Get movie title, description, thumbnail url, status, released.

        $this->applyPreparer(MovieTitlePreparer::class, '.title_name h2', 'text');
        $this->applyPreparer(EpisodeDownloadsPrepare::class, '.dowloads a', 'href');
        return $this->postData;
    }

    public function crawlListLinkDownLoadEpisode(?string $urlDownloadEpisode) {

        if (!$urlDownloadEpisode) return null;

        $this->setPostUrl($urlDownloadEpisode);
        $this->postData = new PostData();

        $this->crawler = $this->request($urlDownloadEpisode, "GET");

        $this->responseHttpStatusCode = $this->getLatestResponse() ? $this->getLatestResponse()->getStatusCode() : null;

        if(!$this->crawler) return null;
        // Get movie title, description, thumbnail url, status, released.
        $this->applyPreparer(EpisodeNameDownloadList::class, '.mirror_link .dowload a', 'text');
        $this->applyPreparer(EpisodeUrlDownloadList::class, '.mirror_link .dowload a', 'href');
        return $this->postData;
    }

    /**
     * @param string $cls Name of a class that extends {@link AbstractPostBotPreparer}.
     * @param $selector
     * @param $attr
     * @return MovieBot
     *
     * @throws \Exception If $cls is not a child of <a href="psi_element://AbstractPostBotPreparer">AbstractPostBotPreparer</a>.
     * @since 1.9.0
     * @since 1.11.0 Type declaration of $cls parameter is added
     */
    private function applyPreparer(string $cls, $selector, $attr) {
        $instance = $this->createPreparer($cls);
        if (!is_a($instance, AbstractPostBotPreparer::class)) {
            throw new \Exception(sprintf('%1$s must be a child of %2$s', $cls, AbstractPostBotPreparer::class));
        }

        /** @var AbstractPostBotPreparer $instance */
        $instance->prepare($selector, $attr);

        return $this;
    }

    /**
     * Create a new preparer instance with its class name
     *
     * @param string $cls Name of a class that extends {@link AbstractPostBotPreparer}.
     * @return AbstractPostBotPreparer|object New instance of the preparer with the specified class
     * @since 1.11.0
     */
    protected function createPreparer(string $cls) {
        return new $cls($this);
    }

    /**
     * Sets {@link $postUrl}
     *
     * @param string $postUrl
     * @since 1.8.0
     * @since 1.11.0 Type declaration of $postUrl parameter is added
     */
    private function setPostUrl(string $postUrl) {
        $this->postUrl = $postUrl;
        $this->postUri = null;
    }

    /**
     * @return Crawler
     */
    public function getCrawler() {
        return $this->crawler;
    }

    public function setCrawler($crawler): void {
        $this->crawler = $crawler;
    }

    /**
     * @return PostData
     */
    public function getPostData() {
        return $this->postData;
    }

    /**
     * @param PostData $postData
     * @since 1.11.0 Type declaration of $postData is added.
     */
    public function setPostData(PostData $postData) {
        $this->postData = $postData;
    }

    /**
     * Get the URL of latest crawled or being crawled post.
     *
     * @return string
     */
    public function getPostUrl() {
        return $this->postUrl;
    }

    public function getCrawlingUrl(): ?string {
        return $this->getPostUrl();
    }

    public function getResponseHttpStatusCode(): ?int {
        return $this->responseHttpStatusCode;
    }

    /**
     * Resolves a URL by considering {@link $postUrl} as base URL.
     *
     * @param string $relativeUrl Relative or full URL that will be resolved against the current post URL.
     * @return string The given URL that is resolved using {@link $postUrl}
     * @see   PostBot::getPostUrl()
     * @see   Utils::resolveUrl()
     * @since 1.8.0
     * @throws \Exception If post URL that will be used to resolve the given URL does not exist.
     */
    public function resolveUrl($relativeUrl) {
        if (!$this->postUrl) {
            throw new \Exception("Post URL does not exist.");
        }

        // If there is no post URI, create it.
        if ($this->postUri === null) {
            $this->postUri = new Uri($this->postUrl);
        }

        return Utils::resolveUrl($this->postUri, $relativeUrl);
    }

    public function createNewSeries($post) {
        include_once(trailingslashit(ABSPATH) . 'wp-includes/pluggable.php');
        include_once(trailingslashit(ABSPATH) . 'wp-includes/post.php');
        // Check series exist
        $series = get_page_by_title($post->getTitle(), OBJECT, 'anime');
        $user_id = get_current_user_id();
        $seriesData = [
            'post_type' => 'anime',
            'post_date'    =>  current_time('mysql'),
            'post_date_gmt' =>  current_time('mysql'),
            'post_author' => $user_id,
            'post_title' => $post->getTitle(),
            'post_content' => $post->getContents()?? '',
            'ping_status'  => $post->getStatus(),
            'post_status' => 'publish',
        ];
        if($series->ID){
            $seriesData['ID'] = $series->ID;
            wp_update_post($seriesData);
            return $series->ID;
        }
        return wp_insert_post($seriesData);
    }

    /**
     * save episode series
     * @param $series_id
     * @param $episodes
     * @param $url
     * @return boolean
     */
    public function saveEpisodeMovie($series_id, $episodes, $url) {
        global $wpdb;
        try {
            for ($episode = 1; $episode <= $episodes; $episode++) {
                $newUrl = '';
                $url = str_replace('/category/', '/', $url);
                $newUrl = $url . '-episode-' . $episode;
                // Check if this URL is already added
                $findQuery = "SELECT id FROM " . $this->getDbTableEpisodeName() . " WHERE series_id = %d AND (url = %s OR url = %s)";
                $count = $wpdb->query($wpdb->prepare($findQuery, [$series_id, trailingslashit($newUrl), rtrim($newUrl, "/")]));

                // If the URL is added, do not insert it again.
                if($count && $count > 0) break;

                $wpdb->insert(
                    $this->getDbTableEpisodeName(),
                    [
                        'series_id'       =>  $series_id,
                        'url'           =>  $newUrl,
                        'created_at'    =>  current_time('mysql')
                    ]
                );
            }
        }catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * get name of table episode
     * @return string
     */
    public function getDbTableEpisodeName() {
        global $wpdb;
        return $wpdb->prefix . DatabaseService::DB_TABLE_EPISODE;
    }

    /**
     * get link download episode
     * @param $urlDownloadEpisode
     * @return PostData|null
     */
    public function getListUrlDownloadEpisode($urlDownloadEpisode) {
        try {
            return $this->crawlListLinkDownLoadEpisode($urlDownloadEpisode);
        } catch (\Exception $e) {
        }
    }

    public function createNewEpisode($post)
    {
        include_once(trailingslashit(ABSPATH) . 'wp-includes/pluggable.php');
        include_once(trailingslashit(ABSPATH) . 'wp-includes/post.php');
        // Check series exist
        $series = get_page_by_title($post->getTitle(), OBJECT, 'anime');
        $user_id = get_current_user_id();
        $seriesData = [
            'post_type'      => 'post',
            'post_date'      =>  current_time('mysql'),
            'post_date_gmt'  =>  current_time('mysql'),
            'post_author'    => $user_id,
            'post_title'     => $post->getTitle(),
            'post_name'      => sanitize_title($post->getTitle()),
            'ping_status'    => 'open',
            'comment_status' => 'open',
            'post_status'    => 'publish',
        ];
        if($series->ID){
            $seriesData['ID'] = $series->ID;
            wp_update_post($seriesData);
            return $series->ID;
        }
        return wp_insert_post($seriesData);
    }
    /**
     * update category for episode
     * @param $episodeId
     * @param $name
     */
    public function updateCategoryForEpisode($episodeId, $name) {
        global $wpdb;

        $args = array(
            'name'                   => $name,
            'get'                    => 'all',
            'number'                 => 1,
            'update_term_meta_cache' => false,
            'orderby'                => 'none',
            'suppress_filter'        => true,
        );
        $terms = get_terms($args);
        $term = array_shift( $terms );
        if($term->term_id){
            $wpdb->insert(
                $wpdb->term_relationships,
                array(
                    'object_id'        => $episodeId,
                    'term_taxonomy_id' => $term->term_id,
                )
            );
            $count = $term->count + 1;
            $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term->term_id ) );
        }
    }
    public function updatePostMeta($episodeId, $seriesId, $chapter = 1)
    {
        Utils::savePostMeta($episodeId, 'ero_seri', $seriesId, true);
        Utils::savePostMeta($episodeId, 'ero_subepisode', 'Sub', true);
        Utils::savePostMeta($episodeId, 'ero_episodebaru', $chapter, true);
        Utils::savePostMeta($episodeId, 'ero_episodetitle', get_the_title($episodeId), true);
    }
}
