<?php
/*
Plugin Name: CT Movie Crawler
Plugin URI: http://example.com
Description: Dowload movie from gogoanime.vc
Author: Chien & Jenick
Text Domain: ct-movie-crawler
Version: 1.0.0
Author URI: http://example.com
*/

// Define constants as: version, plugin url, plugin directory.
define('CT_MOVIE_VERSION', '1.0.0');
define('CT_MOVIE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CT_MOVIE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Call autoload
require 'vendor/autoload.php';
// Load everything
\CTMovie\CTMovie::getInstance();