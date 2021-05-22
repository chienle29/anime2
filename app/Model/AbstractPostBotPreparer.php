<?php

namespace CTMovie\Model;

use CTMovie\Model\Crawling\MovieBot;

/**
 * Class AbstractPostBotPreparer
 * @package CTMovie\Model
 */
abstract class AbstractPostBotPreparer
{
    /** @var MovieBot */
    protected $bot;

    /**
     * @param MovieBot $postBot
     */
    public function __construct(MovieBot $postBot) {
        $this->bot = $postBot;
    }

    /**
     * Prepare the post bot
     *
     * @param $selector
     * @param $attr
     * @return void
     */
    public abstract function prepare($selector, $attr);

    /**
     * Get values for a selector setting. This applies the options box configurations as well.
     *
     * @param string $settingName           Name of the setting from which the selector data will be retrieved
     * @param string $defaultAttr           Attribute value that will be used if the attribute is not found in the
     *                                      settings
     * @param bool   $contentType           See {@link AbstractBot::extractData}
     * @param bool   $singleResult          See {@link AbstractBot::extractData}
     * @param bool   $trim                  See {@link AbstractBot::extractData}
     * @return array|mixed|null             If there are no results, returns null. If $singleResult is true, returns a
     *                                      single result. Otherwise, returns an array.
     * @see AbstractBot::extractValuesForSelectorSetting()
     */
    protected function getValuesForSelectorSetting($settingName, $defaultAttr, $contentType = false,
                                                   $singleResult = false, $trim = true) {

        return $this->bot->extractValuesForSelectorSetting($this->bot->getCrawler(), $settingName, $defaultAttr, $contentType, $singleResult, $trim);
    }

    /**
     * @return MovieBot
     */
    public function getBot() {
        return $this->bot;
    }
}