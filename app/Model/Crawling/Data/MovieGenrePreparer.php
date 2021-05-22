<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

class MovieGenrePreparer extends AbstractPostBotPreparer
{
    /**
     * Prepare the Genre
     *
     * @param $selector
     * @param $attr
     * @return void
     */
    public function prepare($selector, $attr)
    {
        if (!$attr) $attr = 'text';

        if($genres = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, false, true)) {
            $this->bot->getPostData()->setGenre($genres);
        }
    }
}