<?php 

/**
 * Adds the child theme's stylesheet to the WP stylesheet queue
 */
function child_theme_css(){
	wp_register_style("child_main", get_stylesheet_directory_uri() . '/assets/css/custom.min.css', "roots_main");
	wp_enqueue_style("child_main");
}
add_action('wp_enqueue_scripts', 'child_theme_css', 101);

/**
 * Replace the parent's modernizr-load by the child's version
 * NOTE: Not even sure the parent needs a modernizr-load. The parent should probably just be a sort of
 * repository of things to compile from the child. The philosophy will ne to be formalized later on. 
 */
function child_theme_js(){

	// Dequeue the parent theme script loader
	wp_dequeue_script("modernizr-load"); 

	// Register the modernizr load script 
	wp_register_script('child-modernizr-load', get_stylesheet_directory_uri() . '/assets/js/modernizr-load.js', array('modernizr'), null, false);

	// Add javascript config variables to modernizr load script
	$config = array(
		'childPlugins' => get_stylesheet_directory_uri() . '/assets/js/plugins/',
		'childJs' => get_stylesheet_directory_uri() . '/assets/js/',
		'parentPlugins' => get_template_directory_uri() . '/assets/js/plugins/',
		'parentJs' => get_template_directory_uri() . '/assets/js/',
	);
	wp_localize_script('child-modernizr-load', 'WpConfig', $config);
	
	wp_enqueue_script('child-modernizr-load');
}
add_action('wp_enqueue_scripts', 'child_theme_js', 102);

/**
 * Adds a Royal Slider theme stylesheet.
 * Don't forget to add the proper class to the slider in your HTML :
 * 
 * default 			: rsDefault
 * default-inverted : rsDefaultInv
 * Minimal white 	: rsMinW
 * Universal 		: rsUni
 * 
 */
function royal_slider_theme(){

	$themes = array(
		"default" => "default/rs-default.css",
		"default-inverted" => "default-inverted/rs-default-inverted.css",
		"minimal-white" => "minimal-white/rs-minimal-white.css",
		"universal" => "universal/rs-universal.css"
	);

	wp_register_style(
		"royal_slider_theme", 
		get_template_directory_uri() . '/assets/css/royal-slider/skins/' . $themes['minimal-white'], 
		"child_main"
	);
	wp_enqueue_style("royal_slider_theme");
}
add_action('wp_enqueue_scripts', 'royal_slider_theme', 102);