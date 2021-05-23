<?php

namespace CTMovie\Model\Service;

use CURLFile;

/**
 * Class OauthGDrive
 * @package CTMovie\Model
 */
class OauthGDrive
{
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
}