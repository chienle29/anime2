<?php

namespace CTMovie\Model;

use CTMovie\ObjectFactory;
use WP_REST_Response;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;

/**
 * Class RegisterCustomApi
 * @package CTMovie\Model
 */
class RegisterCustomApi extends WP_REST_Controller
{
    const IS_DOWNLOADING = 2;

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $version = '1';
        $namespace = 'ct-movie-crawler/v' . $version;
        $base = 'episode';
        register_rest_route( $namespace, '/' . $base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' )
            )
        ) );
    }

    /**
     *
     * @param $request
     * @return WP_Error|WP_REST_Response
     */
    public function get_items($request)
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