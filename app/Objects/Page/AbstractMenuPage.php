<?php

namespace CTMovie\Objects\Page;

use CTMovie\Environment;
use CTMovie\Utils;

/**
 * Class AbstractMenuPage
 * @package CTMovie\Objects\Page
 */
abstract class AbstractMenuPage
{
    /**
     * @param bool $menuPage    If true, the menu item will be a parent menu item.
     */
    public function __construct($menuPage = false) {
        // Create the menu page
        add_action('admin_menu', function() use ($menuPage) {
            $menuNameColor = $this->getMenuNameColor();

            // Prepare common parameters for add_menu_page and add_submenu_page. add_submenu_page has only 1 more parameter.
            $functionParams = [
                $this->getMenuTitle(),
                $menuNameColor ? "<span style='color: {$menuNameColor}'>" . $this->getMenuTitle() . "</span>" : $this->getMenuTitle(),
                'manage_options',
                $this->getFullPageName(),
                function () {
                    $view = $this->getView();
                    // Add a page action key that can be used when creating nonces and a hidden action for AJAX requests
                    $view->with([
                        'pageTitle'     => $this->getPageTitle(),
                    ]);
                    echo $view->render();
                }
            ];

            if(!$menuPage) {
                // We want this menu item to be a submenu page. Add the missing parameter and call the function.
                $this->pageHookSuffix = call_user_func_array('add_submenu_page', array_merge(
                    ['edit.php?post_type=' . Environment::CT_POST_TYPE],
                    $functionParams
                ));
            } else {
                // We want this menu item to be a parent menu item.
                $this->pageHookSuffix = call_user_func_array('add_menu_page', $functionParams);
            }
        });

        // Listen to AJAX requests
        add_action('wp_ajax_movie_craw', function() {

            $this->handleAJAX();

            wp_die();
        });

        // Listen to POST requests
        add_action('admin_post_tc_general_settings', function() {
            // Verify nonce
            $nonce = Utils::getValueFromArray($_POST, Environment::FORM_NONCE_NAME, false);
            if(!$nonce || !wp_verify_nonce($nonce, 'tc_general_settings')) {
                wp_die("Nonce is invalid.");
            }

            $this->handlePOST();
        });
    }

    /**
     * Override this method to change menu name color.
     *
     * @return string Color of menu name. E.g. #ff4400
     */
    protected function getMenuNameColor() {
        return null;
    }

    /**
     * @return string Menu title for the page
     */
    public abstract function getMenuTitle();

    /**
     * Get page slug
     * @return string
     */
    public abstract function getFullPageName();

    /**
     * Get view for the page.
     * @return mixed Not-rendered blade view for the page
     */
    public abstract function getView();

    /**
     * @return string Page title
     */
    public abstract function getPageTitle();

    /**
     * Handle AJAX requests. <b>Required data should be sent via data key. This returns $_POST["data"].</b>
     *
     * @return array The data in the request
     */
    public function handleAJAX() {
        // We'll return JSON response.
        header('Content-Type: application/json');

        return $_POST;
    }

    /**
     * Handle POST requests
     * @return mixed
     */
    public function handlePOST() {

    }

    /**
     * @param bool $success         Whether the operation is succeeded or not
     * @param string $message       The message to be displayed to the user
     * @param array $queryParams    Additional query parameters that are appended to the redirect URL
     */
    public function redirectBack($success = true, $message = '', $queryParams = []) {
        include_once(trailingslashit(ABSPATH) . 'wp-includes/pluggable.php');
        $params = [];
        $params['success'] = $success ? 'true' : 'false';

        if($message) $params['message'] = urlencode($message);

        wp_redirect($this->getFullPageUrl(array_unique(array_merge($params, $queryParams))));
        exit;
    }

    /**
     * Get full page URL for this page. You can also set additional URL parameters.
     *
     * @param array $args URL parameters as key,value pairs
     * @return string Prepared full URL
     */
    public function getFullPageUrl($args = []) {
        $args = array_merge([
            'page'  =>  $this->getFullPageName(),
        ], $args);
        return untrailingslashit(get_site_url()) . $this->getBaseUrl() . "&" . http_build_query($args);
    }

    /**
     * Get base URL for the menu page item. This can be used to add a sub menu item under the parent menu item.
     *
     * @return string Parent page URL relative to the WordPress index page
     */
    public function getBaseUrl() {
        return '/wp-admin/edit.php?post_type=' . Environment::CT_POST_TYPE;
    }
}