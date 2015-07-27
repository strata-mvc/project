<?php

use Strata\Router\Router;
use App\Model\Adapter\WordpressAdapter;

if (is_admin()) {

    /**
     *  This file should contain callbacks that are applied only in Wordpress' backend.
     */

    // Admin has special styles and scripts.
    add_action('admin_enqueue_scripts', Router::callback('Admin\\CallbackController', 'admin_enqueue_scripts'));
    add_action('add_meta_boxes', Router::callback('Admin\\CallbackController', 'add_meta_boxes'));

    // Bring up our own custom form for managing WPML
    add_action('admin_menu', Router::callback('Admin\\CallbackController', 'admin_menu_customWpml'), 1000);

    // Build a custom selectbox for managing languages.
    $adapter = new WordpressAdapter();
    $adapter->registerTranslatableTypeViewEditCallback(Router::callback('Admin\\CallbackController', 'views_edit_post_or_page'));

    add_filter('wpseo_use_page_analysis',  '__return_false' );

    // Populate the dropdown menu selectbox
    add_filter('acf/load_field/name=dropdown_widget', Router::callback('Admin\\CallbackController', 'acf_load_select_dropdowns'));
}
