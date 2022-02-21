<?php
/**
 * Plugin Name: SF Popup
 * Plugin URI: http://localhost/
 * Description: Plugin for generating popups on web-pages. It supports Hubspot integration to render HS forms in popup.
 * Version: 1.1
 * Author: Serhii Franchuk
 * Author URI: https://localhost/
 */
namespace Sf\Popup;

defined( 'ABSPATH' ) OR exit;

require __DIR__ . '/vendor/autoload.php';

define('Sf\Popup\PHP_MIN_VERSION', '7.2.0');
define('Sf\Popup\PLUGIN_FILE_PATH', 'dp-popup/dp-popup.php');
define('Sf\Popup\PLUGIN_DIR', 'dp-popup');

new Options();

if ( (bool) Options::getOption('debug_mode') && !defined('Sf\Popup\PLUGIN_VERSION') ) {
    define('Sf\Popup\PLUGIN_VERSION', time());
} else {
    define('Sf\Popup\PLUGIN_VERSION', '1.1');
}

require __DIR__. '/functions.php';

if ( version_compare(PHP_VERSION, PHP_MIN_VERSION, '<') ) {
    add_action( 'admin_init', 'Sf\Popup\action_deactivate_plugin' );
    add_action('admin_notices', 'Sf\Popup\popup_admin_notices');
} else {
    register_activation_hook(__FILE__, 'Sf\Popup\action_activate_plugin');

    require __DIR__ . '/includes/post-types.php';

    if ( is_admin() ) {
        add_action('admin_enqueue_scripts', 'Sf\Popup\popup_admin_enqueue_scripts');
    }
    add_action('wp_enqueue_scripts', 'Sf\Popup\popup_enqueue_scripts');

    if ( is_admin() ) {
        new Metaboxes();
    } else {
        add_filter('wp_head', 'Sf\Popup\show_popup');
    }
}
