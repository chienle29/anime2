<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class EpisodeDownloadsPrepare
 * @package CTMovie\Model\Crawling\Data
 */
class EpisodeUrlDownloadList extends AbstractPostBotPreparer
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

        if($urlDownloads = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, false, true)) {
            $this->bot->getPostData()->setEpisodeUrlDownloadList($urlDownloads);
        }
    }
}