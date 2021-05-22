<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class MovieEpisodePrepare
 * @package CTMovie\Model\Crawling\Data
 */
class MovieEpisodePrepare extends AbstractPostBotPreparer
{
    /**
     * Prepare the post title
     *
     * @param $selector
     * @param $attr
     * @return void
     */
    public function prepare($selector, $attr)
    {
        if (!$attr) $attr = 'text';

        if($episodes = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, true, true)) {
            $this->bot->getPostData()->setEpisode($episodes);
        }
    }
}