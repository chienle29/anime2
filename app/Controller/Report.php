<?php

namespace CTMovie\Controller;

use CTMovie\ObjectFactory;
use CTMovie\Utils;
use CTMovie\Model\DatabaseService;

class Report extends \CTMovie\Objects\Page\AbstractMenuPage
{
    /**
     * @return string Menu title for the page
     */
    public function getMenuTitle()
    {
        return __('Report');
    }

    /**
     * @return string Slug for the page
     */
    public function getPageSlug() {
        return 'report';
    }

    /**
     * @inheritDoc
     */
    public function getFullPageName()
    {
        return 'tc_report';
    }

    /**
     * @return string Page title
     */
    public function getPageTitle()
    {
        return __('Report');
    }

    /**
     * Get view for the page.
     *
     * @return mixed Not-rendered blade view for the page
     */
    public function getView()
    {
        $data = $this->getDataView();
        return Utils::view('report/main')->with($data);
    }

    public function getDataView()
    {
        $countUrlCollected = ObjectFactory::databaseService()->getCountUrlCollected();
        $countSaved = ObjectFactory::databaseService()->getCountSeriesSaved();
        $countAnimeUrl = ObjectFactory::databaseService()->getCountAnimeUrl();
        $countAnimeSaved = ObjectFactory::databaseService()->getCountAnimeSaved();
        $result = [
            'countUrl'          => $countUrlCollected,
            'countSeriesSaved'  => $countSaved,
            'countAnimeUrl'     => $countAnimeUrl,
            'countAnimeSaved'   => $countAnimeSaved
        ];
        return $result;
    }
}