<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class MovieTitlePreparer
 * @package CTMovie\Model\Crawling\Data
 */
class MovieTitlePreparer extends AbstractPostBotPreparer
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

        if($title = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, true, true)) {
            $this->bot->getPostData()->setTitle($title);
        }
    }
}