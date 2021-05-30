<?php

namespace CTMovie\Model;

use CTMovie\Environment;
use CTMovie\Model\Crawling\MovieBot;
use CTMovie\Model\Crawling\CategoryBot;
use CTMovie\Model\LauCDN\Connection;
use CTMovie\Model\Media\MediaSavingOptions;
use CTMovie\Model\Media\MediaService;
use CTMovie\Model\Settings;
use CTMovie\ObjectFactory;
use CTMovie\Utils;
use CTMovie\Model\Movie;
use CTMovie\Model\Service\OauthGDrive;

/**
 * Class SchedulingService
 * @package CTMovie\Model
 */
class SchedulingService
{
    /** @var string $eventCollectUrls Cron name used to collects url */
    public $eventCollectUrls = 'tc_event_collect_urls';

    /** @var string $eventCreateSeries Cron name used to create series */
    public $eventCreateSeries = 'tc_event_create_series';

    /** @var string $eventCreateEpisode Cron name used to create episode */
    public $eventCreateEpisode = 'tc_event_create_episode';

    public $eventUploadGDrive = 'tc_event_upload_drive';

    protected $intervals;

    protected $movies;

    /**
     * SchedulingService constructor.
     */
    public function __construct()
    {
        $this->setCRONIntervals();
        //add_action( 'init', 'registerGenreTaxonomy', 0);
        add_action($this->eventCollectUrls, function () {
            $this->tc_executeEventCollectUrls();
        });
        add_action($this->eventCreateSeries, function () {
            $this->createSeriesAndGetMovieUrl();
        });
        add_action($this->eventCreateEpisode, function () {
            $this->getDataEpisodeMovie();
        });
        add_action($this->eventUploadGDrive, function () {
            $this->uploadGoogleDrive();
        });
        // Set what function to call for CRON events
        register_activation_hook(CT_MOVIE_PLUGIN_DIR . 'ct-movie-crawler.php', function () {
            ObjectFactory::schedulingService()->scheduleEvent($this->eventCollectUrls, 'tc_5_minutes');
            ObjectFactory::schedulingService()->scheduleEvent($this->eventCreateSeries, 'tc_5_minutes');
            ObjectFactory::schedulingService()->scheduleEvent($this->eventCreateEpisode, 'tc_5_minutes');
            ObjectFactory::schedulingService()->scheduleEvent($this->eventUploadGDrive, 'tc_10_minutes');
        });
        $this->movies = new Movie();
    }

    /**
     * @return false
     */
    public function uploadGoogleDrive()
    {
        $videoDownloaded = ObjectFactory::databaseService()->getQueueDownloaded();
        foreach ($videoDownloaded as $item) {
            $url = ObjectFactory::lauConnection()->getDriveUrl($item->path_video);
            if (!$url) {
                return false;
            }

            $id = OauthGDrive::uploadFileToGoogleDrive($url, CT_MOVIE_PLUGIN_DIR.$item->path_vide);
            if (!$id) {
                return false;
            }

            $fileId = ObjectFactory::lauConnection()->createFileByDriveId(get_the_title($item->anime_saved_id), $id);
            if (!$fileId) {
                return false;
            }

            $this->createEmbedUrl($fileId, $item->anime_saved_id);
            ObjectFactory::databaseService()->updateUploadedToGDrive($item->id);
        }
    }

    /**
     * Download file, upload to google drive and create embed url to meta data.
     */
    public function uploadAndCreateEmbedUrlForEpisode()
    {
        try {
        $episode = ObjectFactory::databaseService()->getDownloadedEpisodeVideo();
        $videoName = sanitize_title(get_the_title($episode->anime_saved_id)) . '.mp4';
        $url = ObjectFactory::lauConnection()->getDriveUrl($videoName);
        if (!$url) {
            error_log(  'Có lỗi khi get drive url. URL:' . $url);
        }
        error_log( 'drive url: '. $url);
        $id = OauthGDrive::uploadFileToGoogleDrive($url, $episode->path_vide);
        if (!$id) {
            error_log(  'Upload lên google drive fail. ID:' . $id);
        }
        error_log( 'upload file drive: '. $id);
        $fileId = ObjectFactory::lauConnection()->createFileByDriveId(get_the_title($episode->anime_saved_id), $id);
        if (!$fileId) {
            error_log(  'Tạo file trên lậu fail. ID:' . $fileId);
        }
        error_log( 'Tạo file trên lậu: '. $url);
        $this->createEmbedUrl($fileId, $episode->anime_saved_id);
        error_log( 'create embed: ');
        ObjectFactory::databaseService()->updateUploadedToGDrive($episode->id);
        }catch (\Throwable $e) {
            error_log(  'error when create embed url: '. $e->getMessage() );
        } finally {
            $this->removeVideo($episode->path_video);
        }
    }

    /**
     * Download video from url and save path video to movie_episode
     */
    public function downloadEpisodeVideo()
    {
        $episode = ObjectFactory::databaseService()->getDownloadUrl();
        $remoteFile = $episode->download_url;
//        $header = get_headers("$remoteFile");
//        $key = key(preg_grep('/\bLength\b/i', $header));
//        $size = @explode(" ", $header[$key])[1];
//        /**
//         * Link download lỗi hoặc hết hiệu lực, cần update lại link download.
//         */
//        if ($size < 1000) {
//            $url = $episode->url;
//            $settings = [];
//            $bot = new MovieBot($settings);
//            try {
//                $postData = $bot->crawlEpisode($url);
//                if (!$postData) return false;
//
//                $remoteFile = $this->getListUrlDownloadEpisode($postData->getEpisodeUrlDownloads());
//                ObjectFactory::databaseService()->updateEpisodeUrl($episode->id, $remoteFile);
//                $size = $this->retrieve_remote_file_size($remoteFile);
//            } catch (\Throwable $e) {
//                error_log(  'Error when get data episode: '. $e->getMessage() );
//            }
//        }
        echo 'Download remote file url: ' . $remoteFile;
        echo PHP_EOL;

        /**
         * Get file name.
         */
        $filename = sanitize_title(get_the_title($episode->anime_saved_id));

        $filePath = CT_MOVIE_PLUGIN_DIR . $filename . '.mp4';

        /**
         * Tăng thời gian thực thi cho các file có dung lượng lớn.
         */
        set_time_limit(0);
        $response = MediaService::getInstance()->downloadVideo($filePath, $remoteFile);

        /**
         * Download file hoàn tất, cập nhật giá trị is_downloaded = 1.
         */
        if ($response) {
            ObjectFactory::databaseService()->updateStatusDownload(1, $filename, $episode->id);
            return true;
        } else {
            echo 'Download file fail.';
        }
    }

    /**
     * @param $filePath
     */
    public function removeVideo($filePath)
    {
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Cron collects url.
     */
    public function tc_executeEventCollectUrls()
    {
        $args = ['post_type' => Environment::CT_POST_TYPE, 'post_status' => 'publish'];
        $campaignList = get_posts($args);
        try {
            foreach ($campaignList as $campaign) {
                $campaignId = $campaign->ID;
                $settings = get_post_meta($campaignId);
                $categoryUrl = $settings[Settings::CATEGORY_MAP][0];
                if (!empty($settings[Settings::CATEGORY_LAST_CHECKED_URL][0])) {
                    $categoryUrl = $settings[Settings::CATEGORY_LAST_CHECKED_URL][0];
                }
                $bot = new CategoryBot($settings, $campaignId);
                $categoryData = $bot->collectUrls(Utils::prepareUrl($settings[Settings::MAIN_PAGE_URL][0], $categoryUrl));
                foreach ($categoryData->getPostUrlList()->getItems() as $item) {
                    $postUrl = $item->getUrl();
                    if (!$postUrl) continue;

                    if (ObjectFactory::databaseService()->addUrl($campaignId, $postUrl, 0)) {
                        $results[] = $postUrl;
                    }
                }
                if ($categoryData->getNextPageUrl()) {
                    Utils::savePostMeta($campaignId, Settings::CATEGORY_LAST_CHECKED_URL, $categoryData->getNextPageUrl(), true);
                }
            }
        }catch (\Throwable $e) {
            error_log(  'error when collect urls: '. $e->getMessage() );
        }
    }

    /**
     * Collect information and create series.
     */
    public function createSeriesAndGetMovieUrl()
    {
        foreach ($this->movies->getData() as $movie) {
            $campaignId = $movie->post_id;
            $url = $movie->url;
            $settings = get_post_meta($campaignId);
            $bot = new MovieBot($settings, $campaignId);
            try {
                $postData = $bot->crawlPost($url);
                $seriesId = $bot->createNewSeries($postData);
                $series = get_post($seriesId);
                // check have episode and post_type=anime
                if (!empty($postData->getEpisode()) && $series->post_type == 'anime') {
                    $bot->saveEpisodeMovie($seriesId, $postData->getEpisode(), $url);
                }
                $bot->saveFile($postData->getThumbnail(), 300, $seriesId);
                $metaFields = $this->getSeriesMetaFields($postData);
                foreach ($metaFields as $field => $value) {
                    $this->saveSeriesMeta($seriesId, $field, $value);
                }
                $this->saveSeriesTaxonomy($seriesId, [$postData->getTitle()], 'category');
                //$this->saveSeriesTaxonomy($seriesId, $postData->getGenre(), 'genres');
                ObjectFactory::databaseService()->updateSerieSUrl($seriesId, $movie->id);
            } catch (\Throwable $e) {
                error_log(  'error when create series: '. $e->getMessage() );
            }
        }
    }

    /**
     * get episode of series
     */
    public function getDataEpisodeMovie()
    {

        foreach ($this->movies->getEpisodes() as $episode) {
            $url = $episode->url;
            $settings = [];//get_post_meta(24);
            $bot = new MovieBot($settings);
            try {
                $postData = $bot->crawlEpisode($url); // return url download of movie
                if ($postData) {
                    $episodeId = $bot->createNewEpisode($postData);
                    $arr = explode('-', $url);
                    $chapter = end($arr);
                    $bot->updatePostMeta($episodeId, $episode->series_id, $chapter);
                    $bot->updateCategoryForEpisode($episodeId, get_the_title($episode->series_id));
                    ObjectFactory::databaseService()->updateEpisodeStatus($episode->id, $episodeId);
                }
                $downloadUrl = $this->getListUrlDownloadEpisode($postData->getEpisodeUrlDownloads());
                ObjectFactory::databaseService()->updateEpisodeUrl($episode->id, $downloadUrl);
            }catch (\Throwable $e) {
                error_log(  'error when get data episode: '. $e->getMessage() );
            }
        }
    }

    /**
     * get link download episode
     * @param $urlDownloadEpisode
     * @return string
     */
    public function getListUrlDownloadEpisode($urlDownloadEpisode)
    {
        $settings = [];//get_post_meta(24);
        $bot = new MovieBot($settings);
        try {
            $postData = $bot->crawlListLinkDownLoadEpisode($urlDownloadEpisode);
            return $this->getRealDownloadUrl($postData->getEpisodeUrlDownloadList());
        }catch (\Throwable $e) {
            error_log(  'error when url download episode: '. $e->getMessage() );
            return null;
        }
    }

    public function getRealDownloadUrl($urls): string
    {
        return $urls[0];
//        $regex = '/.mp4$/';
//        $result = '';
//        foreach ($urls as $url) {
//            if (preg_match($regex, $url)) {
//                $result = $url;
//                break;
//            }
//        }
//        return $result;
    }

    /**
     * meta data of series
     * @return string[]
     */
    public function getSeriesMetaFields($data)
    {
        return [
            'ero_sub' => 'Sub',
            'ero_mature' => 'No',
            'ero_hot' => 'No',
            'ero_japanese' => $data->getTitle() ?? '',
            'ero_status' => $data->getStatus() ?? 'Ongoing',
            'ero_censor' => 'Censored',
            'ero_type' => 'TV',
            'ero_durasi' => '',
            'ero_skor' => '',
            'ero_tayang' => '',
            'ero_episode' => '',
            'ero_trailer' => '',
            'ero_fansub' => '',
        ];
    }

    /**
     * save meta data of series
     * @param $seriesId
     * @param $meta_key
     * @param $meta_value
     */
    public function saveSeriesMeta($seriesId, $meta_key, $meta_value)
    {
        update_post_meta($seriesId, $meta_key, $meta_value);
    }

    /**
     * save custom taxonomy
     * @param $post_ID
     * @param $terms
     * @param $taxonomy
     * @return mixed
     */
    public function saveSeriesTaxonomy($post_ID, $terms, $taxonomy)
    {
        if (empty($terms)) return true;
        if (!taxonomy_exists($taxonomy)) return true;
        try {
            $termArr = [];
            foreach ($terms as $item) {
                if (term_exists($item, $taxonomy)) continue;
                $term = wp_insert_term(
                    $item,
                    $taxonomy,
                    array(
                        'slug' => sanitize_title($item)
                    )
                );
                $termArr[] = $term['term_id'];
            }
            wp_set_post_terms($post_ID, $termArr, $taxonomy);
        }catch (\Throwable $e) {
            error_log(  'error when save series taxonomy: '. $e->getMessage() );
        }

        return true;
    }

    /**
     * Register genre taxonomy
     */
    function registerGenreTaxonomy()
    {
        $labels = array(
            'name' => _x('Genres', 'Taxonomy General Name', 'text_domain'),
            'singular_name' => _x('Genres', 'Taxonomy Singular Name', 'text_domain'),
            'menu_name' => __('Genres', 'text_domain'),
            'all_items' => __('All Genres', 'text_domain'),
            'parent_item' => __('Parent Genre', 'text_domain'),
            'parent_item_colon' => __('Parent Genre:', 'text_domain'),
            'new_item_name' => __('New Genre Name', 'text_domain'),
            'add_new_item' => __('Add New Genre', 'text_domain'),
            'edit_item' => __('Edit Genre', 'text_domain'),
            'update_item' => __('Update Genre', 'text_domain'),
            'view_item' => __('View Genre', 'text_domain'),
            'separate_items_with_commas' => __('Separate genres with commas', 'text_domain'),
            'add_or_remove_items' => __('Add or remove genres', 'text_domain'),
            'choose_from_most_used' => __('Choose from the most used', 'text_domain'),
            'popular_items' => __('Popular Genres', 'text_domain'),
            'search_items' => __('Search Genres', 'text_domain'),
            'not_found' => __('Not Found', 'text_domain')
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true
        );
        register_taxonomy('genres', array('anime'), $args);
    }

    /**
     * Adds custom time intervals for CRON scheduling.
     */
    private function setCRONIntervals()
    {
        $intervals = $this->getIntervals();
        add_filter('cron_schedules', function ($schedules) use ($intervals) {
            foreach ($intervals as $name => $interval) {
                $schedules[$name] = [
                    'interval' => $interval[1],
                    'display' => $interval[0]
                ];
            }

            return $schedules;
        });
    }

    /**
     * @return array Structured as
     * <b>[ interval_key => [interval_description, interval_in_seconds], interval_key_2 => [ ... ], ... ]</b>
     */
    public function getIntervals()
    {
        if ($this->intervals) return $this->intervals;

        $this->intervals = [
            // Interval Name        Description              Interval in Seconds
            'tc_1_minute' => [__('Every minute'), 60],
            'tc_2_minutes' => [__('Every 2 minutes'), 2 * 60],
            'tc_3_minutes' => [__('Every 3 minutes'), 3 * 60],
            'tc_5_minutes' => [__('Every 5 minutes'), 5 * 60],
            'tc_10_minutes' => [__('Every 10 minutes'), 10 * 60],
            'tc_15_minutes' => [__('Every 15 minutes'), 15 * 60],
            'tc_20_minutes' => [__('Every 20 minutes'), 20 * 60],
            'tc_30_minutes' => [__('Every 30 minutes'), 30 * 60],
            'tc_45_minutes' => [__('Every 45 minutes'), 45 * 60],
            'tc_1_hour' => [__('Every hour'), 60 * 60],
            'tc_2_hours' => [__('Every 2 hours'), 2 * 60 * 60],
            'tc_3_hours' => [__('Every 3 hours'), 3 * 60 * 60],
            'tc_4_hours' => [__('Every 4 hours'), 4 * 60 * 60],
            'tc_6_hours' => [__('Every 6 hours'), 6 * 60 * 60],
            'tc_12_hours' => [__('Twice a day'), 12 * 60 * 60],
            'tc_1_day' => [__('Once a day'), 24 * 60 * 60],
            'tc_2_days' => [__('Every 2 days'), 2 * 24 * 60 * 60],
            'tc_1_week' => [__('Once a week'), 7 * 24 * 60 * 60],
            'tc_2_weeks' => [__('Every 2 weeks'), 2 * 7 * 24 * 60 * 60],
            'tc_1_month' => [__('Once a month'), 4 * 7 * 24 * 60 * 60],
        ];

        return $this->intervals;
    }

    /**
     * Remove a scheduled event. i.e. disable the schedule for an event
     *
     * @param string $eventName Name of the event
     */
    private function removeScheduledEvent($eventName)
    {
        if ($timestamp = wp_next_scheduled($eventName)) {
            wp_unschedule_event($timestamp, $eventName);
        }
    }

    /**
     * Schedules an event after removes the old event, if it exists.
     *
     * @param string $eventName Name of the event
     * @param string $interval One of the registered CRON interval keys
     */
    private function scheduleEvent($eventName, $interval)
    {
        // Try to remove the next schedule.
        $this->removeScheduledEvent($eventName);

        // Schedule the event
        $afterTime = 0;
        if ($eventName == $this->eventCreateSeries) {
            $afterTime = 5;
        }

        if ($eventName == $this->eventCreateEpisode) {
            $afterTime = 10;
        }

        if (!$timestamp = wp_get_schedule($eventName)) {
            wp_schedule_event(time() + $afterTime, $interval, $eventName);
        }
    }

    /**
     * Schedule events with time intervals specified by the user
     */
    public function scheduleEvents()
    {
        $intervalCollectUrls = get_option(Settings::COLLECT_URLS_INTERVAL);
        $intervalCrawlPosts = get_option(Settings::CREATE_SERIES_INTERVAL);

        $this->scheduleEvent($this->eventCollectUrls, $intervalCollectUrls);
        $this->scheduleEvent($this->eventCreateSeries, $intervalCrawlPosts);
    }

    /**
     * Handles scheduling by setting the CRON jobs if scheduling is active, or deleting current jobs if scheduling is
     * disabled.
     */
    public function handleCronEvents()
    {
        // URL collection and post-crawling
        $cronIsActive = get_option(Settings::AUTO_CRAWL_MOVIE) ? true : false;
        if ($cronIsActive) {
            $this->scheduleEvents();
        } else {
            $this->removeURLCollectionAndCrawlingEvents();
        }
    }

    /**
     * Removes scheduled events
     */
    public function removeURLCollectionAndCrawlingEvents()
    {
        $eventNames = [$this->eventCollectUrls, $this->eventCreateSeries];
        foreach ($eventNames as $eventName) {
            $this->removeScheduledEvent($eventName);
        }
    }

    /**
     * @param $driveId
     * @param $animeId
     */
    public function createEmbedUrl($driveId, $animeId)
    {
        $embedUrl = Connection::LAU_EMBED_URL . $driveId;
        $embed = '<iframe src="'.$embedUrl.'" frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%" allowfullscreen="true"></iframe>';
        $data = array(
            0 => array(
                'ab_hostname' => 'lstream',
                'ab_embed' => $embed,
                '_state' => 'expanded'
            )
        );
        $this->saveSeriesMeta($animeId, 'ab_embedgroup', $data);
    }
}