<?php

namespace CTMovie\Objects;

use CTMovie\Environment;
use CTMovie\ObjectFactory;

/**
 * Class SiteMovieTypeCreator
 * @package CTMovie\Objects
 */
class SiteMovieTypeCreator
{
    /**
     * @var SiteMovieTypeCreator
     */
    private static $instance = null;

    /** @var string ID of the meta box that stores all of the settings */
    private $settingsMetaBoxId;

    /**
     * Check whether is post type created.
     * @var boolean
     */
    public $created;

    /** @var string Key of the post type */
    private $postType;

    /**
     * SiteMovieTypeCreator constructor.
     */
    public function __construct()
    {
        $this->postType = Environment::CT_POST_TYPE;
        $this->settingsMetaBoxId    = Environment::CT_CAMPAIGN_SETTING_META_BOX_ID;
    }

    /**
     * @return SiteMovieTypeCreator
     * @since 1.9.0
     */
    public static function getInstance(): SiteMovieTypeCreator {
        if (static::$instance === null) {
            static::$instance = new SiteMovieTypeCreator();
        }

        return static::$instance;
    }

    /**
     * Registers "ct_movie" post type to WordPress
     *
     * @since 1.9.0
     */
    public function create() {
        // If already created, stop.
        if ($this->created) return;

        // Mark as created
        $this->created = true;

        // Add custom post type and configure it
        add_action('init', function () {
            $this->registerPostType();
        });

        // Add the meta boxes storing the settings of the post type
        add_action('add_meta_boxes', function () {
            $this->addMetaBoxes();
        });

        add_action('admin_enqueue_scripts', function ($hook) {
            // Add styles and scripts for post settings
            $this->enqueueScriptsForEditPage($hook);
        });

        // Save options when the post is saved
        add_action('post_updated', function($postId, $postAfter, $postBefore) {
            ObjectFactory::movieService()->postSettingsMetaBox($postId, $postAfter, $postBefore);
        }, 10, 3);
    }

    /**
     * Registers the post type
     *
     * @since 1.9.0
     */
    private function registerPostType() {
        $labels = [
            'name'                  => __('Movies Crawler'),
            'singular_name'         => __('Movie Crawler'),
            'menu_name'             => __('Movie Crawler'),
            'name_admin_bar'        => __('Movie Crawler'),
            'add_new'               => __('Add New'),
            'add_new_item'          => __('Add New Campaign'),
            'new_item'              => __('New Campaign'),
            'edit_item'             => __('Edit Campaign'),
            'view_item'             => __('View Campaign'),
            'all_items'             => __('All Campaigns'),
            'search_items'          => __('Search Campaigns'),
            'parent_item_colon'     => __('Parent Campaigns:'),
            'not_found'             => __('No Campaigns found.'),
            'not_found_in_trash'    => __('No Campaigns found in Trash.')
        ];

        $args = [
            'public'                => false,
            'labels'                => $labels,
            'description'           => __('A custom post type which stores campaigns to be crawled'),
            'menu_icon'             => 'dashicons-tickets-alt',
            'show_ui'               => true,
            'show_in_admin_bar'     => true,
            'show_in_menu'          => true,
            'supports'              => []
        ];

        // Register the post type
        register_post_type($this->getPostType(), $args);

        // Remove text editor
        remove_post_type_support($this->getPostType(), 'editor');
    }

    /**
     * Adds meta boxes storing the settings of the custom post type
     *
     * @since 1.9.0
     */
    private function addMetaBoxes() {
        // Add the meta box that will store the settings of the custom post type
        add_meta_box(
            $this->getSettingsMetaBoxId(),
            __('Settings'),
            function () { echo ObjectFactory::movieService()->getSettingsMetaBox(); },
            $this->getPostType(),
            'normal',
            'high'
        );
    }

    /**
     * @return string See {@link $postType}
     * @since 1.9.0
     */
    public function getPostType(): string
    {
        return $this->postType;
    }

    /**
     * @return string See {@link $settingsMetaBoxId}
     * @since 1.9.0
     */
    public function getSettingsMetaBoxId() {
        return $this->settingsMetaBoxId;
    }

    /**
     * Enqueue styles and scripts of the edit page of the custom post type
     *
     * @param string $hook The hook variable provided by 'admin_enqueue_scripts' filter
     * @since 1.9.0
     */
    private function enqueueScriptsForEditPage($hook) {
        // Check if we are on the custom post page.
        global $post;
        $valid = ($hook == 'post-new.php' && isset($_GET["post_type"]) && $_GET["post_type"] == $this->getPostType()) ||
            ($hook == 'post.php' && $post && $post->post_type == $this->getPostType());
        if(!$valid) return;

        ObjectFactory::assetManager()->addPostSettings();
    }
}