<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\Crawling\Data\MovieUrlList;

/**
 * Class CategoryData
 * @package CTMovie\Model\Crawling\Data
 */
class CategoryData
{
    /** @var MovieUrlList */
    private $postUrlList;

    /** @var string */
    private $nextPageUrl;

    /**
     * @return MovieUrlList
     */
    public function getPostUrlList(): MovieUrlList {
        if ($this->postUrlList === null) {
            $this->postUrlList = new MovieUrlList();
        }

        return $this->postUrlList;
    }

    /**
     * @param MovieUrlList $postUrlList
     */
    public function setPostUrlList(?MovieUrlList $postUrlList) {
        $this->postUrlList = $postUrlList ?: new MovieUrlList();
    }

    /**
     * @return string
     */
    public function getNextPageUrl() {
        return $this->nextPageUrl;
    }

    /**
     * @param string $nextPageUrl
     */
    public function setNextPageUrl($nextPageUrl) {
        $this->nextPageUrl = $nextPageUrl;
    }
}