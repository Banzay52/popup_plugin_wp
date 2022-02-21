<?php
namespace Sf\Popup;

add_action( 'init', '\Sf\Popup\sf_create_taxonomies', 11);
add_action( 'init', '\Sf\Popup\sf_create_post_types', 12);
function sf_create_taxonomies() {
    $taxonomies = array(
        'popup_category' => array(
            'post_types'    => ['sf_popup'],
            'args'          => array(
                'labels'        => array(
                    'name'                        => _x( 'Popup categories', 'taxonomy general name', 'devpro-website' ),
                    'singular_name'               => _x( 'Popup category', 'taxonomy singular name', 'devpro-website' ),
                    'search_items'                =>  __( 'Search popup category', 'devpro-website' ),
                    'popular_items'               => __( 'Popular popup categories', 'devpro-website' ),
                    'all_items'                   => __( 'All popup categories', 'devpro-website' ),
                    'edit_item'                   => __( 'Edit popup category', 'devpro-website' ),
                    'update_item'                 => __( 'Update popup category', 'devpro-website' ),
                    'add_new_item'                => __( 'Add New popup category', 'devpro-website' ),
                    'new_item_name'               => __( 'New popup category name', 'devpro-website' ),
                    'menu_name'                   => __( 'Popup categories', 'devpro-website' ),
                ),
                'hierarchical'  => false,
                'public'        => true,
                'query_var'     => true,
                'show_admin_column' => true,
                'show_in_rest'        => true,
                'show_in_quick_edit' => true,
            )
        ),
    );
    foreach ($taxonomies as $tax_name => $tax_args) {
        register_taxonomy($tax_name, $tax_args['post_types'], $tax_args['args']);
    }
}
function sf_create_post_types() {
    $post_types = array(
        'sf_popup' => array(
            'labels' => array(
                'name'                     => _x('Popups', 'post_type general name', 'devpro-website'),
                'singular_name'            => _x('Popup', 'post_type singular name', 'devpro-website'),
                'add_new'                  => _x('Add popup', 'post_type new name', 'devpro-website'),
                'add_new_item'             => __('Add new popup', 'devpro-website'),
                'edit_item'                => __('Edit popup', 'devpro-website'),
                'new_item'                 => __('New popup', 'devpro-website'),
                'view_item'                => __('View popup', 'devpro-website'),
                'search_items'             => __('Search popup', 'devpro-website'),
                'not_found'                => __('Popup not found', 'devpro-website'),
                'all_items'                => __('All popups', 'devpro-website'),
                'filter_items_list'        => __('Filter popups list', 'devpro-website'),
                'items_list'               => __('Popups list', 'devpro-website'),
                'view_items'               => __('View popups', 'devpro-website'),
                'attributes'               => __('Popup attributes', 'devpro-website'),
                'item_updated'             => __('Popup updated', 'devpro-website'),
                'item_published'           => __('Popup published', 'devpro-website'),
            ),
            'description'         => 'Popups',
            'public' => true,
            'publicly_queryable' => false,
            'show_in_nav_menus'   => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'exclude_from_search' => true,
            'menu_position'       => 108,
            'menu_icon'           => 'dashicons-images-alt2',
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor', 'custom-fields' ], // 'author', 'thumbnail', 'excerpt','trackbacks','revisions','page-attributes','post-formats'
            'taxonomies'          => ['popup_category'],
            'has_archive'         => false,
            'rewrite'             => true,
            'query_var'           => true,
            'capability_type'     => array('sf_popup','sf_popups'),
            'map_meta_cap'        => true,
        ),
    );
    foreach ($post_types as $p_type => $args) {
        register_post_type($p_type, $args);
    }
}

/**
 * Set up capabilities to edit popups
 **/

function sf_popup_add_role_caps() {
    $roles = array( 'editor', 'administrator' );
    // Loop through each role and assign capabilities
    foreach ( $roles as $the_role ) {

        $role = get_role( $the_role );
        if ( ! $role ) continue;
        $role->add_cap( 'read' );
        $role->add_cap( 'read_sf_popup' );
        $role->add_cap( 'read_private_sf_popups' );
        $role->add_cap( 'edit_sf_popup' );
        $role->add_cap( 'edit_sf_popups' );
        $role->add_cap( 'edit_others_sf_popups' );
        $role->add_cap( 'edit_published_sf_popups' );
        $role->add_cap( 'publish_sf_popups' );
//        if ( $the_role !== 'insight_editor' ) {
        $role->add_cap( 'delete_others_sf_popups' );
        $role->add_cap( 'delete_private_sf_popups' );
        $role->add_cap( 'delete_published_sf_popups' );
//        }
    }
}
add_action('admin_init','\Sf\Popup\sf_popup_add_role_caps',999);
