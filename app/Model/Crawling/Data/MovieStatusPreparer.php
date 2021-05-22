<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class MovieStatusPreparer
 * @package CTMovie\Model\Crawling\Data
 */
class MovieStatusPreparer extends AbstractPostBotPreparer
{
    /**
     * Prepare the movie status.
     *
     * @param $selector
     * @param $attr
     * @return void
     */
    public function prepare($selector, $attr)
    {
        if (!$attr) $attr = 'text';

        if($status = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, true, true)) {
            $status = str_replace('Status: ', '', $status);
            $this->bot->getPostData()->setStatus($status);
        }
    }
}