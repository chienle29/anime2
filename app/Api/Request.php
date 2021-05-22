<?php

namespace CTMovie\Api;

interface Request
{
    /**
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function get(string $url, array $params);

    /**
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function post(string $url, array $params);
}