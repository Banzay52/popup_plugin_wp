<?php

namespace Sf\Popup;

use const Sf\Popup\PLUGIN_FILE_PATH;
use const Sf\Popup\PHP_MIN_VERSION;
use const Sf\Popup\PLUGIN_VERSION;

function action_deactivate_plugin(){
    deactivate_plugins( PLUGIN_FILE_PATH );
    flush_rewrite_rules();
}

function action_activate_plugin() {
    flush_rewrite_rules();
}

function popup_admin_notices() {
    ob_start();
    ?>
    <div id="message" class="notice notice-error is-dismissible">
        <p>Plugin DP Popup works on PHP version <?php echo PHP_MIN_VERSION; ?> or higher</p>
    </div>
    <?php
    echo ob_get_clean();
}

function popup_enqueue_scripts() {
    $p_type = get_post_type();
    if ( in_array($p_type, Options::getOption('popup_enabled_post_types', ['page'])) &&
        Options::getOption('popup_global_enable') &&
        !get_post_meta(get_the_id(), 'disable_popup', true) ||
        ( is_search() && Options::getOption('popup_enabled_search_page')) ||
        ( is_404() && Options::getOption('popup_enabled_404_page')) ) {

        wp_enqueue_script('dp-popup', plugins_url('/assets/js/dp-popup.js', __FILE__), array(), PLUGIN_VERSION, true);
        wp_enqueue_style('dp-popup', plugins_url('/assets/css/dp-popup.css', __FILE__), array(), PLUGIN_VERSION);

        $time_to_show = Options::getOption('timeout_on_site_open');
        $time_to_show_after_close = Options::getOption('timeout_on_close_popup') * 60;
        $debug_mode = Options::getOption('debug_mode');

        wp_localize_script('dp-popup', 'sf_popup', ['time_to_show' => $time_to_show, 'time_to_show_after_close' => $time_to_show_after_close, 'debug_mode' => $debug_mode]);
    }
}
function popup_admin_enqueue_scripts() {
    wp_enqueue_style('dp-popup-admin', plugins_url('/assets/css/dp-popup-admin.css', __FILE__), array(), PLUGIN_VERSION);
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-datepicker','https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css',false,"1.9.0",false);
}

function show_popup($content): string {
    $popup = new Popup();

    return $popup->getPopupHtml() . $content;
}

/**
 * Add Popup active column to popup posts list in admin
 */

add_filter( 'manage_sf_popup_posts_columns', 'Sf\Popup\popup_post_columns' );
function popup_post_columns( $columns ) {
    unset($columns['comments']);
    $columns['popup_active'] = __( 'Active' );

    return $columns;
}

add_action( 'manage_sf_popup_posts_custom_column', 'Sf\Popup\popup_column', 10, 2);
function popup_column( $column, $post_id ) {
    $value = get_post_meta( $post_id, 'popup_active', true );
    switch ($column) {
        case 'popup_active' :
            echo ($value === false || empty($value)) ? "" : "<span>Active</span>";
            break;
        default:
            break;
    }
}
add_filter( 'wpseo_sitemap_exclude_post_type', 'Sf\Popup\sitemap_exclude_post_type', 10, 2 );
function sitemap_exclude_post_type( $value, $post_type ) {
    if ( $post_type == 'sf_popup' ) return true;
}
