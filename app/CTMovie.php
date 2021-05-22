<?php

namespace CTMovie;

/**
 * Class CTMovie
 * @package CTMovie
 */
class CTMovie
{
    /**
     * @var CTMovie
     */
    private static $instance = null;

    /**
     * CTMovie constructor.
     */
    private function __construct()
    {
        ObjectFactory::getInstance();
    }

    /**
     * @return CTMovie
     * @since 1.9.0
     */
    public static function getInstance() {
        if (static::$instance === null) {
            static::$instance = new CTMovie();
        }

        return static::$instance;
    }
}