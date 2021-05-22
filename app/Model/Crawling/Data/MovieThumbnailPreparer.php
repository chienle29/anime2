<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class MovieThumbnailPreparer
 * @package CTMovie\Model\Crawling\Data
 */
class MovieThumbnailPreparer extends AbstractPostBotPreparer
{
    /**
     * Prepare the thumbnail
     *
     * @param $selector
     * @param $attr
     * @return void
     */
    public function prepare($selector, $attr)
    {
        if (!$attr) $attr = 'src';

        if($src = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, true, true)) {
            $this->bot->getPostData()->setThumbnail($src);
        }
    }
}