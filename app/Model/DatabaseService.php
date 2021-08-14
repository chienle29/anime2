<?php

namespace CTMovie\Model;

/**
 * Class DatabaseService
 * @package CTMovie\Model
 */
class DatabaseService
{
    const DB_TABLE_URLS = 'ct_movie_urls';
    const DB_TABLE_EPISODE = 'ct_movie_episode';

    public function __construct()
    {
        // Make sure the DB is up-to-date. This is important, because when the plugin is updated automatically from
        // the admin panel, activation hook is not called. So, we make sure that the DB is up-to-date every time
        // this class is constructed.
        $this->createDbTables();
    }

    /**
     * Create the database tables required for the plugin
     */
    public function createDbTables() {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $tableUrls = $this->getDbTableUrlsName();
        $tableEpisode = $this->getDbTableEpisodeName();
        /**
         * Query creating URLs table. This table will be used to store URLs to be crawled and already crawled. A way to
         * keep the track of the URLs. This way, we won't be adding a post URL more than once.
         *
         * IE11 allows max of 2083 chars for a URL. However, to be on the safe side, let's define URL field as 2560 chars.
         * @see http://stackoverflow.com/a/417184/2883487
         */
        $sql = "CREATE TABLE {$tableUrls} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            url varchar(2560) NOT NULL,
            is_saved boolean NOT NULL DEFAULT FALSE,
            saved_post_id bigint(20) UNSIGNED,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charsetCollate;";

        /**
         * create table to save episode of movie
         */
        $sqlEpisode = "CREATE TABLE {$tableEpisode} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            series_id bigint(20) NOT NULL,
            url varchar(2560) NOT NULL,
            download_url varchar(1000),
            is_downloaded boolean NOT NULL DEFAULT FALSE, 
            saved boolean NOT NULL DEFAULT FALSE,
            anime_saved_id bigint(20) UNSIGNED,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charsetCollate;";

        require_once(str_replace("/", DIRECTORY_SEPARATOR, (trailingslashit(ABSPATH) . 'wp-admin/includes/upgrade.php')));
        dbDelta($sql);
        dbDelta($sqlEpisode);
    }

    /**
     * @return string
     */
    public function getDbTableUrlsName() {
        global $wpdb;
        return $wpdb->prefix . static::DB_TABLE_URLS;
    }

    /**
     * @return string
     */
    public function getDbTableEpisodeName() {
        global $wpdb;
        return $wpdb->prefix . static::DB_TABLE_EPISODE;
    }

    /**
     * Add a URL to the urls table
     *
     * @param int $postId ID of the custom post type (site)
     * @param string $url The URL
     * @param string $thumbnailUrl Thumbnail URL for the post
     * @param int $categoryId Category ID in which the URL will be added when crawled
     * @param bool $isSaved
     * @return int ID of the inserted row
     */
    public function addUrl($postId, $url, $isSaved = false) {
        global $wpdb;

        // Check if this URL is already added
        $findQuery = "SELECT id FROM " . $this->getDbTableUrlsName() . " WHERE post_id = %d AND (url = %s OR url = %s)";
        $count = $wpdb->query($wpdb->prepare($findQuery, [$postId, trailingslashit($url), rtrim($url, "/")]));

        // If the URL is added, do not insert it again.
        if($count && $count > 0) return false;

        $wpdb->insert(
            $this->getDbTableUrlsName(),
            [
                'post_id'       =>  $postId,
                'url'           =>  $url,
                'is_saved'      =>  $isSaved,
                'created_at'    =>  current_time('mysql'),
                'updated_at'    =>  current_time('mysql'),
            ]
        );

        return $wpdb->insert_id;
    }

    /**
     * @return array|object|null
     */
    public function getMovieUrlsData()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT post_id, url, id FROM " . $this->getDbTableUrlsName() . " WHERE is_saved = %d LIMIT 50", 0));

        if(!empty($results)) return $results;
        return null;
    }

    /**
     * @return array|object|null
     */
    public function getEpisodes()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT series_id, url, id FROM " . $this->getDbTableEpisodeName() . " WHERE saved = %d LIMIT 50", 0));

        if(!empty($results)) return $results;
        return null;
    }

    /**
     * @param int $seriesId
     * @param int $campaignId
     * @return bool|int
     */
    public function updateSeriesUrl(int $seriesId, int $campaignId)
    {
        global $wpdb;
        $tableName = $this->getDbTableUrlsName();
        $sql = "UPDATE {$tableName} SET is_saved = 1, saved_post_id = {$seriesId} WHERE id = {$campaignId}";
        return $wpdb->query($sql);
    }

    /**
     * @return int
     */
    public function getCountUrlCollected()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) count FROM {$this->getDbTableUrlsName()} "));

        if(!empty($results)) {
            return $results[0]->count;
        };
        return 0;
    }

    /**
     * @return int
     */
    public function getCountSeriesSaved()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) count FROM {$this->getDbTableUrlsName()} WHERE is_saved = 1"));

        if(!empty($results)) {
            return $results[0]->count;
        };
        return 0;
    }

    /**
     * @return int
     */
    public function getCountAnimeUrl()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) count FROM {$this->getDbTableEpisodeName()}"));

        if(!empty($results)) {
            return $results[0]->count;
        };
        return 0;
    }

    /**
     * @return int
     */
    public function getCountAnimeSaved()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) count FROM {$this->getDbTableEpisodeName()} WHERE is_downloaded = 1"));

        if(!empty($results)) {
            return $results[0]->count;
        };
        return 0;
    }

    /**
     * @param $id
     * @param $episodeId
     * @return bool|int
     */
    public function updateEpisodeStatus($id, $episodeId)
    {
        global $wpdb;
        $tableName = $this->getDbTableEpisodeName();
        $sql = "UPDATE {$tableName} SET saved = 1, anime_saved_id = {$episodeId} WHERE id = {$id}";
        return $wpdb->query($sql);
    }

    /**
     * @param $id
     * @param $url
     * @return bool|int
     */
    public function updateEpisodeUrl($id, $url)
    {
        global $wpdb;
        $tableName = $this->getDbTableEpisodeName();
        $sql = "UPDATE {$tableName} SET download_url = '{$url}' WHERE id = {$id}";
        return $wpdb->query($sql);
    }

    /**
     * @return array|object|null
     */
    public function getDownloadUrl()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT id, anime_saved_id, download_url, url, is_downloaded FROM " . $this->getDbTableEpisodeName() . " WHERE saved = %d AND is_downloaded = %d LIMIT 1", 1, 0));

        if(!empty($results)) return $results;
        return null;
    }

    /**
     * @param $id
     * @return int
     */
    public function checkInProcessDownload($id) {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT is_downloaded FROM " . $this->getDbTableEpisodeName() . " WHERE id = %d ", $id));
        if(!empty($results))
            return (int)$results[0]->is_downloaded;
        return 0;
    }

    /**
     * get a episode downloaded video
     * @return array|object|null
     */
    public function getDownloadedEpisodeVideo() {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT id, anime_saved_id, download_url, path_video FROM " . $this->getDbTableEpisodeName() . " WHERE path_video IS NOT NULL AND is_downloaded = %s AND is_uploaded = %s ORDER BY RAND() LIMIT 1", [1, 0]));

        if(!empty($results)) return $results[0];
        return null;
    }

    /**
     * @param $episodeId
     * @param $path
     * @return bool|int
     */
    public function updateDownloaded($episodeId, string $path) {
        global $wpdb;
        $tableName = $this->getDbTableEpisodeName();
        $sql = "UPDATE {$tableName} SET is_downloaded = 1  , path_video = '{$path}' WHERE id = {$episodeId}";
        return $wpdb->query($sql);
    }

    /**
     * @param $episodeId
     * @return bool|int
     */
    public function updateUploadedToGDrive($episodeId) {
        global $wpdb;
        $tableName = $this->getDbTableEpisodeName();
        $sql = "UPDATE {$tableName} SET is_uploaded = 1  WHERE id = {$episodeId}";
        $wpdb->query($sql);
        $results = $wpdb->get_results($wpdb->prepare("SELECT series_id FROM " . $tableName . " WHERE id = %d ", $episodeId));
        if(!empty($results)) {
            $seriesId = (int)$results[0]->series_id;
            wp_update_post(array(
                'ID'    =>  $seriesId,
                'post_status'   =>  'publish'
            ));
        }
        return true;
    }

    /**
     * @param $path
     * @param $id
     * @return bool|int
     */
    public function updatePathVideo($path, $id)
    {
        global $wpdb;
        $tableName = $this->getDbTableEpisodeName();
        $sql = "UPDATE {$tableName} SET path_video = '{$path}'  WHERE id = {$id}";
        return $wpdb->query($sql);
    }

    /**
     * @param $status
     * @param $id
     * @return bool|int
     */
    public function updateStatusDownload($status, $id)
    {
        global $wpdb;
        $tableName = $this->getDbTableEpisodeName();
        $sql = "UPDATE {$tableName} SET is_downloaded = {$status} WHERE id = {$id}";
        return $wpdb->query($sql);
    }

    /**
     * @return mixed|null
     */
    public function getQueueDownloaded()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $this->getDbTableEpisodeName() . " WHERE is_downloaded = %s LIMIT 10", 1));

        if(!empty($results)) return $results;
        return null;
    }
}