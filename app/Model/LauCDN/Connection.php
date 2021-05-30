<?php

namespace CTMovie\Model\LauCDN;

use CTMovie\Api\Request;
use CTMovie\Model\Settings;

class Connection implements Request
{
    const API_KEY = '4b51d4b7-e5a5-4097-89f2-9c6fc4730fde';

    const LAU_DRIVE_URL = 'https://dash.laucdn.xyz/api/v1/tokens/upload-url?file_name=';

    const LAU_CREATE_FILE_URL = 'https://dash.laucdn.xyz/api/v1/files';

    const LAU_EMBED_URL = 'https://lstream.media/';

    /**
     * @inheritDoc
     */
    public function get(string $url, array $params)
    {
        $response = wp_remote_get($url, $params);
        if (is_wp_error($response)) {
            //Log
            return null;
        }
        return json_decode($response['body']);
    }

    /**
     * @inheritDoc
     */
    public function post(string $url, array $params)
    {
        $response = wp_remote_post($url, $params);
        if (is_wp_error($response)) {
            //Log
            return null;
        }
        return json_decode($response['body']);
    }

    /**
     * Get drive upload url
     * @param $fileName
     * @return mixed
     */
    public function getDriveUrl($fileName)
    {
        $url = self::LAU_DRIVE_URL . $fileName . '&api_key='.get_option(Settings::LAU_API_KEY);
        $params = [
            'headers'     => [
                'Content-Type' => 'application/json',
            ],
            'timeout'     => 600,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'data_format' => 'body',
        ];
        $response = $this->get($url, $params);
        return $response->data->url;
    }

    /**
     * Create file via google drive public file id.
     * @param $title
     * @param $driveId
     */
    public function createFileByDriveId($title, $driveId)
    {
        $url = self::LAU_CREATE_FILE_URL . '?api_key='.get_option(Settings::LAU_API_KEY);
        $body = [
            'title'     => $title,
            'driveId'   => $driveId,
            'proxyOnly' => false,
            'label'     => 'English',
            'language'  => 'en'
        ];
        $body = wp_json_encode($body);
        $params = [
            'body'        => $body,
            'headers'     => [
                'Content-Type' => 'application/json',
            ],
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'data_format' => 'body',
        ];
        $response = $this->post($url, $params);
        if ($response) return $response->data->file_id_hash;
        return false;
    }
}