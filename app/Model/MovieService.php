<?php

namespace CTMovie\Model;

use CTMovie\Objects\SiteMovieTypeCreator;
use CTMovie\Utils;
use CTMovie\Model\Settings;
use WP_Post;

/**
 * Class MovieService
 * @package CTMovie\Model
 */
class MovieService
{
    /**
     * MovieService constructor.
     */
    public function __construct()
    {
        SiteMovieTypeCreator::getInstance()->create();
    }

    /**
     * Prepares and returns HTML for site settings meta box
     * @return string HTML
     */
    public function getSettingsMetaBox() {
        global $post;

        // Set Tiny MCE settings so that it allows custom HTML codes and keeps them unchanged
        add_filter('tiny_mce_before_init', function($settings) {

            // Disable autop to keep all valid HTML elements
            $settings['wpautop'] = false;

            // Don't remove line breaks
            $settings['remove_linebreaks'] = false;

            // Format the HTML
            $settings['apply_source_formatting'] = true;

            // Convert newline characters to BR
            $settings['convert_newlines_to_brs'] = true;

            // Don't remove redundant BR
            $settings['remove_redundant_brs'] = false;

            // Pass back to WordPress
            return $settings;
        });

        $settings = get_post_meta($post->ID);

        $viewVars = [
            'postId'                        => $post->ID,
            'settings'                      => $settings,
            'categories'                    => Utils::getCategories()
        ];
        return Utils::view('site-settings/main')->with($viewVars)->render();
    }

    /**
     * Handles HTTP POST requests made by create/edit page (where site settings meta box is)
     *
     * @param int $postId
     * @param WP_Post $postAfter
     * @param WP_Post $postBefore
     */
    public function postSettingsMetaBox($postId, $postAfter, $postBefore)
    {
        // Do not run if the post is moved to trash.
        if ($postAfter->post_status == 'trash') return;

        // Do not run if the post is restored.
        if ($postBefore->post_status == 'trash') return;

        $this->saveSettings($postId, $_POST);
    }

    /**
     * @param int   $postId
     * @param array $settings Settings retrieved from form. $_POST can be directly supplied. The values must be slashed
     *                        because WP's post meta saving function requires slashed data.
     * @return array
     * @since 1.8.0
     */
    public function saveSettings($postId, $settings)
    {
        $data = $settings;
        $success = true;
        $message = '';

        $keys = $this->getMetaKey();

        // Save options
        foreach ($data as $key => $value) {
            if (in_array($key, $this->getMetaKey())) {
                if(is_array($value)) $value = array_values($value);
                Utils::savePostMeta($postId, $key, $value, true);

                // Remove the key, since it is saved.
                unset($keys[array_search($key, $keys)]);
            }
        }
        // Save last checked category next page url
        Utils::savePostMeta($postId, Settings::CATEGORY_LAST_CHECKED_URL, null, true);

        // Delete the metas which are not set
        foreach($keys as $key) delete_post_meta($postId, $key);

        return [
            "message" => $message,
            "success" => $success
        ];
    }

    /**
     * Get meta keys
     * @return array
     */
    public function getMetaKey(): array
    {
        return [
            Settings::MAIN_PAGE_URL,
            Settings::CATEGORY_MAP,
            Settings::CT_TOOLS_CATEGORY_ID,
            Settings::MOVIE_URL_IN_CATE_SELECTOR,
            Settings::MOVIE_URL_IN_CATE_SELECTOR_ATTR,
            Settings::NEXT_PAGE_SELECTOR,
            Settings::NEXT_PAGE_SELECTOR_ATTR,
            Settings::THUMBNAIL_SELECTOR,
            Settings::THUMBNAIL_SELECTOR_ATTR,
            Settings::UNNECESSARY_ELEMENT,
            Settings::MOVIE_TITLE_SELECTOR,
            Settings::MOVIE_TITLE_SELECTOR_ATTR,
            Settings::MOVIE_URL_SELECTOR,
            Settings::MOVIE_URL_SELECTOR_ATTR,
            Settings::MOVIE_DESCRIPTION_SELECTOR,
            Settings::MOVIE_DESCRIPTION_SELECTOR_ATTR,
            Settings::MOVIE_STATUS_SELECTOR,
            Settings::MOVIE_STATUS_SELECTOR_ATTR,
            Settings::MOVIE_RELEASED_SELECTOR,
            Settings::MOVIE_RELEASED_SELECTOR_ATTR,
            Settings::MOVIE_CHAPTER_URL_SELECTOR,
            Settings::MOVIE_CHAPTER_URL_SELECTOR_ATTR,
            Settings::MOVIE_EPISODE,
            Settings::MOVIE_EPISODE_ATTR
        ];
    }
}