<?php 

function child_theme_css(){
	wp_register_style("child_main", get_stylesheet_directory_uri() . '/assets/css/custom.css', "roots_main");
	wp_enqueue_style("child_main");
}
add_action('wp_enqueue_scripts', 'child_theme_css', 101);