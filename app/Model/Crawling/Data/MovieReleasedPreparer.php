<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class MovieReleasedPreparer
 * @package CTMovie\Model\Crawling\Data
 */
class MovieReleasedPreparer extends AbstractPostBotPreparer
{
    /**
     * Prepare the movie release.
     *
     * @param $selector
     * @param $attr
     * @return void
     */
    public function prepare($selector, $attr)
    {
        if (!$attr) $attr = 'text';

        if($released = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, true, true)) {
            $released = str_replace('Released: ', '', $released);
            $this->bot->getPostData()->setReleased($released);
        }
    }
}