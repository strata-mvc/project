<?php

use Strata\Router\Router;

/**
 *  This file should contain callbacks that are applied both in Wordpress' frontend
 *  as well as it's backend.
 *
 */

add_action('widgets_init', Router::callback("CallbackController", "widgets_init"));


// WPML Plugin
// -----------
// Remove flags and unnecessary WPML UI
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
