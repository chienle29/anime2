<?php

use CTMovie\Model\Crawling\MovieBot;
use CTMovie\ObjectFactory;

class DownloadVideo
{
    protected $url = 'http://test1.vi-magento.com/wp-json/ct/v1/';

    /**
     * Download video from url and save path video to movie_episode
     */
    public function execute()
    {
        $path = dirname(__FILE__);
        do {
            $episode = $this->getData();

            if (count($episode) == 1) {
                $episode = $episode[0];
            }
            if ($episode) {
                $remoteUrl = $episode['download_url'];
            }
        } while ($episode);
    }

    /**
     * Lấy dữ liệu từ site nguồn.
     * @return bool|array
     */
    protected function getData()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url.'episode',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
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
}

$download = new DownloadVideo();
$download->execute();