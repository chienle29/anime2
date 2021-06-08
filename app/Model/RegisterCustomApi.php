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
    /**
     * Trạng thái đang download.
     */
    const IS_DOWNLOADING = 2;

    /**
     * RegisterCustomApi constructor.
     */
    public function __construct()
    {
        /**
         * Đăng ký api, lấy dữ liệu.
         */
        add_action('rest_api_init', function () {
            register_rest_route('ct/v1','/episode', [
                'methods'   =>  "GET",
                'callback'  =>  [$this, 'getEpisodeData']
            ]);
            /**
             * Đăng ký api, cập nhật thông tin cho phim.
             */
            register_rest_route('ct/v1','/update_iframe', [
                'methods'   =>  "POST",
                'callback'  =>  [$this, 'updateIframe']
            ]);
        });
    }

    /**
     * Lấy dữ liệu để script download video.
     * @return WP_Error|WP_REST_Response
     */
    function getEpisodeData()
    {
        /**
         * Mỗi request chỉ lấy 1 record có anime_saved_id khác null và is_downloaded = 1
         * Để tránh trường hợp nhiều server cùng lấy 1 record.
         */
        $episode = ObjectFactory::databaseService()->getDownloadUrl();

        $episodeId = $episode->id;

        /**
         * Sau khi lấy dữ liệu rồi thì cập nhật trạng thái thành đang download (2)
         * Để các script ở server sẽ không lấy record này nữa.
         */
        ObjectFactory::databaseService()->updateStatusDownload(self::IS_DOWNLOADING, $episodeId);

        /**
         * Nếu không có dữ liệu để trả về.
         */
        if (empty($episode)) {
            return new WP_Error( 'empty_episode', 'there is no episode.', ['status' => 404]);
        }

        $response = new WP_REST_Response($episode);
        $response->set_status(200);

        return $response;
    }

    /**
     * Cập nhật lại iframe của phim.
     * Cập nhật lại trạng thái của record, để biết rằng record này đã được download hoàn tất.
     * @param $request
     * @return WP_REST_Response
     */
    public function updateIframe($request)
    {
        /**
         * Lấy dữ liệu được gửi sang từ script php.
         */
        $iframeUrl  = $request['iframe'];
        $postId     = $request['post_id'];
        $recordId   = $request['record_id'];

        /**
         * Tạo embed.
         */
        $embed = '<iframe src="'.$iframeUrl.'" frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%" allowfullscreen="true"></iframe>';
        $data = array(
            0 => array(
                'ab_hostname' => 'lstream',
                'ab_embed' => $embed,
                '_state' => 'expanded'
            )
        );
        /**
         * Cập nhật lại phim với embed đã tạo ở trên.
         */
        update_post_meta($postId, 'ab_embedgroup', $data);

        /**
         * Cập nhật lại trạng thái của record - đã download hoàn tất.
         */
        ObjectFactory::databaseService()->updateUploadedToGDrive($recordId);

        /**
         * Trả response về cho script.
         */
        $response = ['response' => true];
        $res = new WP_REST_Response($response);
        $res->set_status(200);

        return $res;
    }
}