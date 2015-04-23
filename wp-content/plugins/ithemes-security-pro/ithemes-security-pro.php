<?php
/*
	Plugin Name: iThemes Security Pro
	Plugin URI: https://ithemes.com/security
	Description: Protect your WordPress site by hiding vital areas of your site, protecting access to important files, preventing brute-force login attempts, detecting attack attempts and more.
	Version: 1.14.19
	Text Domain: it-l10n-ithemes-security-pro
	Domain Path: /languages
	Author: iThemes.com
	Author URI: https://ithemes.com
	Network: True
	License: GPLv2
	iThemes Package: ithemes-security-pro
	Copyright 2015  iThemes  (email : info@ithemes.com)
*/



function ithemes_repository_name_updater_register( $updater ) {
	$updater->register( 'ithemes-security-pro', __FILE__ );
}

add_action( 'ithemes_updater_register', 'ithemes_repository_name_updater_register' );

require( dirname( __FILE__ ) . '/lib/updater/load.php' ); //Loads the iThemes updater

if ( is_admin() ) {

	require( dirname( __FILE__ ) . '/lib/icon-fonts/load.php' ); //Loads iThemes fonts
	require( dirname( __FILE__ ) . '/lib/one-version/index.php' ); //Only have one version of the plugin

}

require_once( dirname( __FILE__ ) .  '/core/class-itsec-core.php' );
new ITSEC_Core( __FILE__, __( 'iThemes Security Pro', 'it-l10n-ithemes-security-pro' ) );
