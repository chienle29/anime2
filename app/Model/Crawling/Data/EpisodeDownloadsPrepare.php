<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\AbstractPostBotPreparer;

/**
 * Class EpisodeDownloadsPrepare
 * @package CTMovie\Model\Crawling\Data
 */
class EpisodeDownloadsPrepare extends AbstractPostBotPreparer
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

        if($urlDownloadsEpisode = $this->bot->extractData($this->bot->getCrawler(), $selector, $attr, false, true, true)) {
            $this->bot->getPostData()->setEpisodeUrlDownloads($urlDownloadsEpisode);
        }
    }
}