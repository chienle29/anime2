<?php

namespace CTMovie\Controller;

use CTMovie\ObjectFactory;
use CTMovie\Utils;
use CTMovie\Environment;

class GeneralSetting extends \CTMovie\Objects\Page\AbstractMenuPage
{
    private static $ALL_GENERAL_SETTINGS = null;

    protected $settings;

    public function __construct($menuPage = false)
    {
        parent::__construct($menuPage);
        $this->settings = Utils::getOptionKeys();
    }

    /**
     * @return string Menu title for the page
     */
    public function getMenuTitle()
    {
        return __('General Settings');
    }

    /**
     * @return string Slug for the page
     */
    public function getPageSlug() {
        return 'general_settings';
    }

    /**
     * @inheritDoc
     */
    public function getFullPageName()
    {
        return 'tc_general_settings';
    }

    /**
     * @return string Page title
     */
    public function getPageTitle()
    {
        return __('General Settings');
    }

    /**
     * Get view for the page.
     *
     * @return mixed Not-rendered blade view for the page
     */
    public function getView()
    {
        // Add assets
        ObjectFactory::assetManager()->addGeneralSetting();

        return Utils::view('general-settings/main')->with($this->getSettingsPageVariables());
    }

    /**
     * Get the variables necessary for general settings page. Variables are
     *      'settings' (all options for general settings),
     *      'postStatuses' (available post statuses to select),
     *      'authors' (available authors to select as post author),
     *      'intervals' ()
     * @param bool $isGeneralPage Set false when getting settings for a site's settings page. By this way, you can get
     *      only necessary variables.
     * @return array
     */
    public static function getSettingsPageVariables($isGeneralPage = true) {
        $result = [];

        if($isGeneralPage) {
            $allSettings = static::getAllGeneralSettings();

            $settings = $allSettings;
            // If a setting's value is array, then, to comply with post meta traditions (since form items are designed
            // for post meta), make the value an array and add a new entry to the array, which is the value's serialized form.
            // So, by this way, form items will work as expected.
            foreach($allSettings as $key => $mSetting) {
                if(is_array($mSetting)) {
                    $serialized = serialize($mSetting);
                    $mSetting = [];
                    $mSetting[] = $serialized;
                    $settings[$key] = $mSetting;
                }
            }

            $result['settings'] = $settings;
        }

        // CRON intervals
        if($isGeneralPage) {
            $intervals = [];
            foreach (ObjectFactory::schedulingService()->getIntervals() as $key => $interval) {
                $intervals[$key] = $interval[0];
            }
            $result['intervals'] = $intervals;
        }

        $postTypes = get_post_types();
        if(isset($postTypes[Environment::CT_POST_TYPE])) unset($postTypes[Environment::CT_POST_TYPE]);
        $result["postTypes"] = $postTypes;
        $result['pageActionKey'] = 'tc_general_settings';

        return $result;
    }

    /**
     * Get all settings related to the general settings page
     * @return array General settings for the content crawler
     */
    public static function getAllGeneralSettings() {
        if(static::$ALL_GENERAL_SETTINGS) return static::$ALL_GENERAL_SETTINGS;
        global $wpdb;

        // Get all options related to the content crawler
        $options = $wpdb->get_results("
            SELECT option_name, option_value
            FROM $wpdb->options
            WHERE option_name LIKE '_ct_%'
        ");

        // When the options are saved by update_option function, some characters are escaped by slashes. Since we
        // get all values directly with a MySQL query (without an unescape operation), we need to unescape them.
        $optionsPrepared = [];
        foreach ((array)$options as $o) {
            $optionsPrepared[$o->option_name] =
                is_serialized($o->option_value) ?
                    Utils::arrayStripSlashes(unserialize($o->option_value)) :
                    (is_array($o->option_value) ?
                        Utils::arrayStripSlashes($o->option_value) :
                        stripslashes($o->option_value)
                    );
        }

        static::$ALL_GENERAL_SETTINGS = $optionsPrepared;

        return static::$ALL_GENERAL_SETTINGS;
    }

    public function handlePOST() {
        parent::handlePOST();

        $data       = $_POST;
        $message    = '';
        $success    = true;

        // Set or remove CRON events
        ObjectFactory::schedulingService()->handleCronEvents();

        $this->handleSaveRequest($data, $success, $message);

        // Redirect back
        $this->redirectBack($success, $message, []);
    }

    /**
     * Handles "save general settings" request made by clicking to the "save" button in the general settings page
     *
     * @param array  $data    Post data
     * @param bool   $success This variable will be true if the operation has succeeded
     * @param string $message This variable will have the response message that should be sent to the user.
     * @since 1.9.0
     */
    private function handleSaveRequest($data, &$success, &$message) {
        $keys = $this->settings;
        $message = __("Thiết lập đã được cập nhật.");
        $success = true;

        // Save options
        foreach ($data as $key => $value) {
            if (in_array($key, $this->settings)) {
                update_option($key, $value, false);

                // Remove the key, since it is saved.
                unset($keys[array_search($key, $keys)]);
            }
        }

        // Delete options which are not set
        foreach($keys as $key) delete_option($key);
    }
}