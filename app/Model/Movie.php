<?php

namespace CTMovie\Model;

use CTMovie\ObjectFactory;

/**
 * Class Movie
 * @package CTMovie\Model
 */
class Movie
{
    /**
     * @var array|object|null
     */
    protected $data;

    /**
     * Movie constructor.
     */
    public function __construct()
    {
        $this->data = ObjectFactory::databaseService()->getMovieUrlsData();
    }

    /**
     * @return array|object|null
     */
    public function getData()
    {
        return $this->data;
    }

    public function getEpisodes() {
        return ObjectFactory::databaseService()->getEpisodes();
    }
}