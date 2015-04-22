<?php

/*
Central management of options storage and registered packages.
Written by Chris Jean for iThemes.com
Version 1.2.0

Version History
	1.0.0 - 2013-09-19 - Chris Jean
		Split off from the old Ithemes_Updater_Init class.
	1.0.1 - 2013-09-20 - Chris Jean
		Fixed bug where the old ithemes-updater-object global was being referenced.
	1.1.0 - 2013-10-04 - Chris Jean
		Enhancement: Added handler for GET query variable: ithemes-updater-force-minor-update.
		Bug Fix: Changed URL regex for applying the CA patch to only apply to links for api.ithemes.com and not the S3 links.
		Bug Fix: A check to ensure that the $GLOBALS['ithemes_updater_path'] variable is set properly.
		Misc: Updated file reference for ca/cacert.crt to ca/roots.crt.
	1.2.0 - 2013-10-23 - Chris Jean.
		Enhancement: Added the quick_releases setting.
		Enhancement: Added an explicit flush when the ithemes-updater-force-minor-update query variable is used
		Misc: Removed the show_on_sites setting as it is no longer needed.
*/


class Ithemes_Updater_Settings {
	private $option_name = 'ithemes-updater-cache';
	
	private $packages = array();
	private $new_packages = array();
	private $options = false;
	private $options_modified = false;
	private $do_flush = false;
	private $initialized = false;
	
	private $default_options = array(
		'server-cache'   => 30,
		'expiration'     => 0,
		'timestamp'      => 0,
		'packages'       => array(),
		'update_plugins' => array(),
		'update_themes'  => array(),
		'use_ca_patch'   => false,
		'use_ssl'        => true,
		'quick_releases' => false,
	);
	
	
	public function __construct() {
		$GLOBALS['ithemes-updater-settings'] = $this;
		
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}
	
	public function init() {
		if ( $this->initialized )
			return;
		
		$this->initialized = true;
		
		if ( ! isset( $GLOBALS['ithemes_updater_path'] ) )
			$GLOBALS['ithemes_updater_path'] = dirname( __FILE__ );
		
		$this->load();
		
		do_action( 'ithemes_updater_register', $this );
		
		$this->new_packages = array_diff( array_keys( $this->packages ), $this->options['packages'] );
		
		
		if ( isset( $_GET['ithemes-updater-force-quick-release-update'] ) && ! isset( $_GET['ithemes-updater-force-minor-update'] ) )
			$_GET['ithemes-updater-force-minor-update'] = $_GET['ithemes-updater-force-quick-release-update'];
		
		if ( isset( $_GET['ithemes-updater-force-minor-update'] ) ) {
			if ( $_GET['ithemes-updater-force-minor-update'] ) {
				$this->options['force_minor_version_update'] = time() + 3600;
				$this->update_options( $this->options );
				
				$this->flush( 'forced minor version update' );
			}
			else {
				unset( $this->options['force_minor_version_update'] );
				$this->update_options( $this->options );
				
				$this->flush( 'unset forced minor version update' );
			}
		}
		else if ( isset( $this->options['force_minor_version_update'] ) && ( $this->options['force_minor_version_update'] < time() ) ) {
			unset( $this->options['force_minor_version_update'] );
			$this->update_options( $this->options );
		}
		
		
		if ( ! empty( $_GET['ithemes-updater-force-refresh'] ) && current_user_can( 'manage_options' ) )
			$this->flush( 'forced' );
		else if ( empty( $this->options['expiration'] ) || ( $this->options['expiration'] <= time() ) )
			$this->flush( 'expired' );
		else if ( $this->is_expired( $this->options['timestamp'] ) )
			$this->flush( 'got stale' );
		else if ( ! empty( $this->new_packages ) )
			$this->flush( 'new packages' );
	}
	
	public function load() {
		if ( false !== $this->options )
			return;
		
		$this->options = get_site_option( $this->option_name, false );
		
		if ( ( false === $this->options ) || ! is_array( $this->options ) )
			$this->options = array();
		
		$this->options = array_merge( $this->default_options, $this->options );
		
		if ( 0 == $this->options['timestamp'] )
			$this->update();
	}
	
	public function shutdown() {
		if ( $this->do_flush )
			$this->flush();
		
		if ( $this->options_modified )
			update_site_option( $this->option_name, $this->options );
	}
	
	public function queue_flush() {
		$this->do_flush = true;
	}
	
	public function flush( $reason = '' ) {
		$this->do_flush = false;
		
		$this->update();
	}
	
	public function update() {
		$this->init();
		
		require_once( $GLOBALS['ithemes_updater_path'] . '/updates.php' );
		
		Ithemes_Updater_Updates::run_update();
	}
	
	public function get_options() {
		$this->init();
		
		return $this->options;
	}
	
	public function get_option( $var ) {
		$this->init();
		
		if ( isset( $this->options[$var] ) )
			return $this->options[$var];
		
		return null;
	}
	
	public function update_options( $updates ) {
		$this->init();
		
		$this->options = array_merge( $this->options, $updates );
		$this->options_modified = true;
	}
	
	public function update_packages() {
		$this->update_options( array( 'packages' => array_keys( $this->packages ) ) );
	}
	
	public function get_packages() {
		return $this->packages;
	}
	
	public function get_new_packages() {
		return $this->new_packages;
	}
	
	public function filter_update_plugins( $update_plugins ) {
		if ( ! is_object( $update_plugins ) )
			return $update_plugins;
		
		if ( ! isset( $update_plugins->response ) || ! is_array( $update_plugins->response ) )
			$update_plugins->response = array();
		
		if ( $this->do_flush )
			$this->flush();
		
		if ( ! is_array( $this->options ) || ! isset( $this->options['update_plugins'] ) )
			$this->load();
		
		if ( isset( $this->options['update_plugins'] ) && is_array( $this->options['update_plugins'] ) )
			$update_plugins->response = array_merge( $update_plugins->response, $this->options['update_plugins'] );
		
		return $update_plugins;
	}
	
	public function filter_update_themes( $update_themes ) {
		if ( ! is_object( $update_themes ) )
			return $update_themes;
		
		if ( ! isset( $update_themes->response ) || ! is_array( $update_themes->response ) )
			$update_themes->response = array();
		
		if ( $this->do_flush )
			$this->flush();
		
		if ( ! is_array( $this->options ) || ! isset( $this->options['update_themes'] ) )
			$this->load();
		
		if ( isset( $this->options['update_themes'] ) && is_array( $this->options['update_themes'] ) )
			$update_themes->response = array_merge( $update_themes->response, $this->options['update_themes'] );
		
		return $update_themes;
	}
	
	public function register( $slug, $file ) {
		$this->packages[$slug][] = $file;
	}
	
	public function add_ca_patch_to_curl_opts( $handle ) {
		$url = curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );
		
		if ( ! preg_match( '{^https://(api|downloads)\.ithemes\.com}', $url ) )
			return;
		
		curl_setopt( $handle, CURLOPT_CAINFO, $GLOBALS['ithemes_updater_path'] . '/ca/roots.crt' );
	}
	
	public function enable_ssl_ca_patch() {
		add_action( 'http_api_curl', array( $this, 'add_ca_patch_to_curl_opts' ) );
	}
	
	public function disable_ssl_ca_patch() {
		remove_action( 'http_api_curl', array( $this, 'add_ca_patch_to_curl_opts' ) );
	}
	
	private function is_expired( $timestamp ) {
		$page = empty( $_GET['page'] ) ? $GLOBALS['pagenow'] : $_GET['page'];
		
		switch ( $page ) {
			case 'update-core.php' :
			case 'ithemes-licensing' :
				$timeout = 60;
				break;
			case 'plugins.php' :
			case 'themes.php' :
			case 'update.php' :
				$timeout = 3600;
				break;
			default :
				$timeout = 12 * 3600;
		}
		
		$time = time();
		$age = $time - $timestamp;
		$time_remaining = $timeout - $age;
		
		if ( $timestamp <= ( time() - $timeout ) )
			return true;
		
		return false;
	}
}

new Ithemes_Updater_Settings();
