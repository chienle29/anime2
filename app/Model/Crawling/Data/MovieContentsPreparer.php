<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class MovieContentsPreparer
 * @package CTMovie\Model\Crawling\Data
 */
class MovieContentsPreparer extends AbstractPostBotPreparer
{
    /**
     * Prepare the post description
     *
     * @param $selector
     * @param $attr
     * @return void
     */
    public function prepare($selector, $attr)
    {
        if (!$attr) $attr = 'text';

        if($contents = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, true, true)) {
            $contents = str_replace('Plot Summary: ', '', $contents);
            $this->bot->getPostData()->setContents($contents);
        }
    }
}