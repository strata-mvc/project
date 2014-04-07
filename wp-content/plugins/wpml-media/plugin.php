<?php
/*
Plugin Name: WPML Media
Plugin URI: http://wpml.org/
Description: Add multilingual support for Media files
Author: ICanLocalize
Author URI: http://wpml.org
Version: 2.0.2
*/

if (defined('WPML_MEDIA_VERSION')) {
	return;
}

define('WPML_MEDIA_PATH', dirname(__FILE__));

if (is_admin()) {

	require WPML_MEDIA_PATH . '/inc/constants.inc';
	require WPML_MEDIA_PATH . '/inc/wpml-media-dependencies.class.php';
	require WPML_MEDIA_PATH . '/inc/wpml-media-upgrade.class.php';
	require WPML_MEDIA_PATH . '/inc/wpml-media.class.php';

	$WPML_media = new WPML_Media();
}