<?php


class DownloadVideo
{
    const SERVER_URL = 'http://anime.com/';

    const API_KEY = '4b51d4b7-e5a5-4097-89f2-9c6fc4730fde';

    const LAU_DRIVE_URL = 'https://dash.laucdn.xyz/api/v1/tokens/upload-url?file_name=';

    const LAU_CREATE_FILE_URL = 'https://dash.laucdn.xyz/api/v1/files';

    const LAU_EMBED_URL = 'https://lstream.media/';
    /**
     * Download video from url and save path video to movie_episode
     */
    public function execute()
    {
        do {
            $episode = $this->getData();
            if ($episode) {
                echo 'episode '. $episode->url ."\nis downloading...";
                $this->downloadEpisodeVideo($episode);
            }
        } while ($episode);
        echo 'download finished';
    }

    /**
     * Lấy dữ liệu từ site nguồn.
     * @return false|mixed
     */
    protected function getData()
    {
        $api_url = self::SERVER_URL.'wp-json/ct/v1/episode'.'?a=' . rand(1, 100).rand(0, 10);
        // Read JSON file
        $json_data = file_get_contents($api_url);
        // Decode JSON data into PHP array
        $response_data = json_decode($json_data);
        if($response_data->id)
            return $response_data;

        return false;
    }

    public function downloadEpisodeVideo($episode)
    {
        $remoteFile = $episode->download_url ?? '';
        $header = get_headers("$remoteFile");
        $key = key(preg_grep('/\bLength\b/i', $header));
        $size = @explode(" ", $header[$key])[1];
        $path = dirname(__FILE__).'/';
        /**
         * Link download lỗi hoặc hết hiệu lực, cần update lại link download.
         */
        if ($size < 1000) {
            echo "\ncan't download this link it's expired or denied\n";
            return null;
        }
        echo PHP_EOL;
        /**
         * Get file name.
         */
        $temp = explode('/', $episode->url);
        $filename = end($temp);//sanitize_title(get_the_title($episode->anime_saved_id));

        $filePath = $path . $filename . '.mp4';

        /**
         * Tăng thời gian thực thi cho các file có dung lượng lớn.
         */
        set_time_limit(0);
        $response = $this->startdownloadVideo($filePath, $remoteFile);

        /**
         * Download file hoàn tất, cập nhật giá trị is_downloaded = 1.
         */
        if ($response) {
              $dataUpdateStatus = [
                  'id' => $episode->id,
                  'update_status' => 1
              ];
              $update_url = self::SERVER_URL . 'wp-json/ct/v1/update_episode';
              // update status for episode
              $this->sendPostRequest($update_url, $dataUpdateStatus);
              $this->uploadAndCreateEmbedUrlForEpisode($episode, $filePath);
            print "Download finish \n";
        } else {
            echo 'Download file fail.';
        }
    }

    /**
     * @param $filePath
     * @param $remoteUrl
     * @return bool
     */
    public function startdownloadVideo($filePath, $remoteUrl)
    {
        $header = get_headers("$remoteUrl");
        $pp = "0";
        $key = key(preg_grep('/\bLength\b/i', $header));
        $tbytes = @explode(" ", $header[$key])[1];
        echo " Target size: " . floor((($tbytes / 1000) / 1000)) . " Mb || " . floor(($tbytes / 1000)) . " Kb";
        echo PHP_EOL;
        $remote = fopen($remoteUrl, 'r');
        echo 'File path: ' . $filePath;
        $local = fopen($filePath, 'w');
        $read_bytes = 0;
        echo PHP_EOL;
        while (!feof($remote)) {
            $buffer = fread($remote, intval($tbytes));
            fwrite($local, $buffer);
            $read_bytes += 2048;
            $progress = min(100, 100 * $read_bytes / $tbytes);
            $progress = substr($progress, 0, 6) * 4;
            $shell = 10;
            $rt = $shell * $progress / 100;
            echo " \033[35;2m\e[0m Downloading: [" . round($progress, 3) . "%] " . floor((($read_bytes / 1000) * 4)) . "Kb ";
            if ($pp === $shell) {
                $pp = 0;
            };
            if ($rt === $shell) {
                $rt = 0;
            };
            echo str_repeat("█", $rt) . str_repeat("=", ($pp++)) . ">@\r";
            usleep(1000);
        }
        echo " \033[35;2m\e[0mDone [100%]  " . floor((($tbytes / 1000) / 1000)) . " Mb || " . floor(($tbytes / 1000)) . " Kb   \r";
        echo PHP_EOL;
        fclose($remote);
        fclose($local);

        return true;
    }

    /**
     * Download file, upload to google drive and create embed url to meta data.
     * @param $episode
     * @param $filePath
     * @return false
     */
    public function uploadAndCreateEmbedUrlForEpisode($episode, $filePath)
    {
        try {
            $temp = explode('/', $episode->url);
            $videoName = end($temp);
            $url = $this->getDriveUrl($videoName.'.mp4');
            if (!$url) {
                print "Có lỗi xảy ra khi lấy drive url \n";
                return false;
            }
            print "Upload google drive... \n";
            $id = $this->uploadFileToGoogleDrive($url, $filePath);
            if (!$id) {
                print "Upload google drive thất bại \n";
                return false;
            }
            print "Tạo file trên lậu... \n";
            $fileId = $this->createFileByDriveId($videoName, $id);
            if (!$fileId) {
                print "Tạo file trên lậu fail \n";
                return false;
            }
            // update embed iframe
            $dataUpdateIframe = [
                'record_id' => $episode->id,
                'post_id' => $episode->anime_saved_id,
                'iframe'  => self::LAU_EMBED_URL . $fileId
            ];
            $url = self::SERVER_URL . 'wp-json/ct/v1/update_iframe';
            $this->sendPostRequest($url, $dataUpdateIframe);
        }catch (\Throwable $e) {
           echo  'something error in process upload episode'. $e->getMessage();
        } finally {
            print "Tạo file trên lậu done \n";
            $this->removeFile($filePath);
        }
    }

    /**
     * @param $filePath
     */
    public function removeFile($filePath)
    {
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Get drive upload url
     * @param $fileName
     * @return mixed
     */
    public function getDriveUrl($fileName)
    {
        $url = self::LAU_DRIVE_URL . $fileName . '&api_key='.self::API_KEY;
        $response = $this->sendGetRequest($url);
        if($response['success'] && isset($response['data']['url']) )
            return $response['data']['url'];
        return null;
    }

    /**
     * send post request
     * @param $url
     * @param $data
     * @return mixed
     */
    public function sendPostRequest($url, $data)
    {
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result);
    }

    /**
     * send get request
     * @param $url
     * @return false|mixed
     */
    public function sendGetRequest($url){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 600,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type'   => 'application/json',
            ]
        ));

        $result = curl_exec($curl);
        curl_close($curl);
        if ($result) {
            return json_decode($result, true);
        }
        return false;
    }

    /**
     * Upload file to drive. See more: https://developers.google.com/drive/api/v3/manage-uploads
     */
    public static function uploadFileToGoogleDrive($url, $file)
    {
        set_time_limit(0);
        $fileSize = filesize($file);

        $curl = curl_init();
        $read = fopen($file, 'r');
        $data = fread($read, $fileSize);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 9999,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Content-Type'   => 'video/mp4',
                'Content-Length' => $fileSize
            ]
        ));

        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true)['id'];
    }

    /**
     * @param $title
     * @param $driveId
     * @return false|string
     */
    public function createFileByDriveId($title, $driveId)
    {
        $url = self::LAU_CREATE_FILE_URL . '?api_key='.self::API_KEY;
        $body = [
            'title'     => $title,
            'driveId'   => $driveId,
            'proxyOnly' => false,
            'label'     => 'English',
            'language'  => 'en'
        ];
        $response = $this->sendPostRequest($url, $body);
        if($response->data->file_id_hash)
            return $response->data->file_id_hash;

        return false;
    }
}

$download = new DownloadVideo();
// start process create upload video to Lau
$download->execute();