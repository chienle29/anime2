<?php

namespace CTMovie\Model;

use CTMovie\ObjectFactory;
use WP_REST_Response;
use WP_Error;

/**
 * Class RegisterCustomApi
 * @package CTMovie\Model
 */
class RegisterCustomApi
{
    const IS_DOWNLOADING = 2;

    public function __construct()
    {
        add_action('rest_api_init', 'getDataForScriptDownload');
    }

    /**
     * Đăng ký Api để lấy dữ liệu trả về cho script php.
     */
    public function getDataForScriptDownload()
    {
        register_rest_route('ct-movie-crawler/v1', sprintf(
            '/%s',
            'episode'
        ), array(
                array(
                    'methods' => "GET",
                    'callback' => array($this, 'getEpisodeData'),
                )
            )
        );
    }

    /**
     *
     * @param $request
     * @return WP_Error|WP_REST_Response
     */
    public function getEpisodeData($request)
    {
        /**
         * Mỗi request chỉ lấy 1 record có anime_saved_id khác null và is_downloaded = 1
         * Để tránh trường hợp nhiều server cùng lấy 1 record.
         */
        $episode = ObjectFactory::databaseService()->getDownloadUrl();

        $episodeId = $episode->id;

        /**
         * Sau khi lấy dữ liệu rồi thì cập nhật trạng thái thành đang download (2)
         * Để các script ở server khác sẽ không lấy record này nữa.
         */
        ObjectFactory::databaseService()->updateStatusDownload(self::IS_DOWNLOADING, $episodeId);

        /**
         * Nếu không có dữ liệu để trả về.
         */
        if (empty($episode)) {
            return new WP_Error('empty_episode', 'there is no episode.', array('status' => 404));
        }

        $response = new WP_REST_Response($episode);
        $response->set_status(200);

        return $response;
    }
}