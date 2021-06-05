<?php

use CTMovie\Model\Crawling\MovieBot;
use CTMovie\ObjectFactory;

class DownloadVideo
{
    /**
     * Download video from url and save path video to movie_episode
     */
    public function execute()
    {
        $path = dirname(__FILE__);
        $arr = str_replace('wp-content/plugins/ct-movie-crawler', '', $path);
        require_once($arr . 'wp-load.php');
        print "Downloading... \n";
        ObjectFactory::schedulingService()->downloadEpisodeVideo();
        print "Complete. \n";
    }
}
$download = new DownloadVideo();
$download->execute();