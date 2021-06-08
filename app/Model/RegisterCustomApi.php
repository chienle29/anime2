<?php

namespace CTMovie\Model;

use CTMovie\Model\Crawling\MovieBot;
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
     * Lấy dữ liệu cho script.
     * @return false|WP_Error|WP_REST_Response
     */
    function getEpisodeData()
    {
        /**
         * Mỗi request chỉ lấy 1 record có anime_saved_id khác null và is_downloaded = 1
         * Để tránh trường hợp nhiều server cùng lấy 1 record.
         */
        $episode = ObjectFactory::databaseService()->getDownloadUrl();

        /**
         * Lấy phần tử đầu tiên của mảng.
         */
        if (count($episode) == 1) {
            $episode = $episode[0];
        }

        /**
         * Nếu không có dữ liệu để trả về.
         */
        if (empty($episode)) {
            return new WP_Error( 'empty_episode', 'there is no episode.', ['status' => 404]);
        }
        $remoteUrl = $episode->download_url;

        $header = get_headers("$remoteUrl");
        $key = key(preg_grep('/\bLength\b/i', $header));
        $size = @explode(" ", $header[$key])[1];
        /**
         * Link download lỗi hoặc hết hiệu lực, cần update lại link download.
         */
        if ($size < 1000) {
            $url = $episode->url;
            $settings = [];
            $bot = new MovieBot($settings);
            try {
                $postData = $bot->crawlEpisode($url);
                if (!$postData) {
                    return new WP_Error( 'link_download_die', 'Link download died.', ['status' => 404]);
                }

                $remoteUrl = $this->getListUrlDownloadEpisode($postData->getEpisodeUrlDownloads());
                ObjectFactory::databaseService()->updateEpisodeUrl($episode->id, $remoteUrl);
            } catch (\Throwable $e) {
                error_log('Error when get data episode: ' . $e->getMessage());
            }
        }

        /**
         * Cập nhật lại link download.
         */
        $episode->download_url = $remoteUrl;

        $episodeId = $episode->id;
        /**
         * Sau khi lấy dữ liệu rồi thì cập nhật trạng thái thành đang download (2)
         * Để các script ở server sẽ không lấy record này nữa.
         */
        ObjectFactory::databaseService()->updateStatusDownload(self::IS_DOWNLOADING, $episodeId);

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

    /**
     * @param $urls
     * @return string
     */
    public function getRealDownloadUrl($urls): string
    {
        return $urls[0];
    }
}