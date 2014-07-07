<?php
/*
Plugin Name: New RoyalSlider
Plugin URI: http://dimsemenov.com/plugins/royal-slider/wordpress/
Description: Professional image gallery and content slider plugin. Activation and deactivation of plugin keeps data. "Delete" removes all data and settings completely.
Author: Dmitry Semenov
Version: 3.1.3
Author URI: http://dimsemenov.com
*/
if ( defined( 'ABSPATH' ) && !class_exists("NewRoyalSliderMain") ) {

	if(!defined('NEW_ROYALSLIDER_WP_VERSION')) {
		define( 'NEW_ROYALSLIDER_WP_VERSION', '3.1.3' );
	}

	if(!defined('NEW_ROYALSLIDER_UPDATE_URL')) {
		define( 'NEW_ROYALSLIDER_UPDATE_URL', 'http://dimsemenov.com/private/rsupdate.php' );
	}

	if(!defined('NEW_ROYALSLIDER_DIRNAME')) {
		define( 'NEW_ROYALSLIDER_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
	}

	if(!defined('NEW_ROYALSLIDER_PLUGIN_PATH')) {
		define( 'NEW_ROYALSLIDER_PLUGIN_PATH', trailingslashit( dirname( __FILE__ ) ) );
	}
	if(!defined('NEW_ROYALSLIDER_PLUGIN_URL')) {
		define( 'NEW_ROYALSLIDER_PLUGIN_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
	}

	require_once NEW_ROYALSLIDER_PLUGIN_PATH . 'classes/NewRoyalSliderMain.php';	

	$new_royalSlider = new NewRoyalSliderMain(__FILE__);		
	function get_new_royalslider($id) {
		global $new_royalSlider;		
		return $new_royalSlider->get_slider($id);
	}
	function register_new_royalslider_files($id) {
		global $new_royalSlider;		
		return $new_royalSlider->push_script($id);
	}

}
?>