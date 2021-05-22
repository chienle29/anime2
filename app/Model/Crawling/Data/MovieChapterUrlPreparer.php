<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class MovieChapterUrlPreparer
 * @package CTMovie\Model\Crawling\Data
 */
class MovieChapterUrlPreparer extends AbstractPostBotPreparer
{
    /**
     * Prepare the movie status
     *
     * @param $selector
     * @param $attr
     * @return void
     */
    public function prepare($selector, $attr)
    {
        if (!$attr) $attr = 'href';

        if($chapterUrls = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, false, true)) {
            $a = 1;
        }
    }
}