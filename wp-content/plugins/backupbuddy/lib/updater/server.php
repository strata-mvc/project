<?php

/*
Provides an easy to use interface for communicating with the iThemes updater server.
Written by Chris Jean for iThemes.com
Version 1.0.2

Version History
	1.0.0 - 2013-04-11 - Chris Jean
		Release ready
	1.0.1 - 2013-06-21 - Chris Jean
		Updated the http_build_query call to force a separator of & in order to avoid issues with servers that change the arg_separator.output php.ini value.
	1.0.2 - 2013-09-19 - Chris Jean
		Updated ithemes-updater-object to ithemes-updater-settings.
*/


class Ithemes_Updater_Server {
	private static $secure_server_url = 'https://api.ithemes.com/updater';
	private static $insecure_server_url = 'http://api.ithemes.com/updater';
	
	private static $password_iterations = 8;
	
	
	public static function activate_package( $username, $password, $packages ) {
		$query = array(
			'user' => $username
		);
		
		$data = array(
			'auth_token' => self::get_password_hash( $username, $password ),
			'packages'   => $packages,
		);
		
		return Ithemes_Updater_Server::request( 'package-activate', $query, $data );
	}
	
	public static function deactivate_package( $username, $password, $packages ) {
		$query = array(
			'user' => $username
		);
		
		$data = array(
			'auth_token' => self::get_password_hash( $username, $password ),
			'packages'   => $packages,
		);
		
		return Ithemes_Updater_Server::request( 'package-deactivate', $query, $data );
	}
	
	public static function get_package_details( $packages ) {
		$query = array();
		
		$data = array(
			'packages' => $packages
		);
		
		return Ithemes_Updater_Server::request( 'package-details', $query, $data );
	}
	
	public static function request( $action, $query = array(), $data = array() ) {
		if ( isset( $data['auth_token'] ) )
			$data['iterations'] = self::$password_iterations;
		
		
		$default_query = array(
			'wp'        => $GLOBALS['wp_version'],
			'site'      => get_bloginfo( 'url' ),
			'timestamp' => time(),
		);
		
		if ( is_multisite() )
			$default_query['ms'] = 1;
		
		$query = array_merge( $default_query, $query );
		$request = "/$action/?" . http_build_query( $query, '', '&' );
		
		$post_data = array(
			'request' => json_encode( $data ),
		);
		
		$remote_post_args = array(
			'timeout' => 10,
			'body'    => $post_data,
		);
		
		
		$options = array(
			'use_ca_patch' => false,
			'use_ssl'      => true,
		);
		
		$patch_enabled = $GLOBALS['ithemes-updater-settings']->get_option( 'use_ca_patch' );
		
		if ( $patch_enabled )
			$GLOBALS['ithemes-updater-settings']->disable_ssl_ca_patch();
		
		
		$response = wp_remote_post( self::$secure_server_url . $request, $remote_post_args );
		
		if ( is_wp_error( $response ) ) {
			$GLOBALS['ithemes-updater-settings']->enable_ssl_ca_patch();
			$response = wp_remote_post( self::$secure_server_url . $request, $remote_post_args );
			
			if ( ! is_wp_error( $response ) )
				$options['use_ca_patch'] = true;
		}
		
		if ( is_wp_error( $response ) ) {
			$response = wp_remote_post( self::$insecure_server_url . $request, $remote_post_args );
			
			$options['use_ssl'] = false;
		}
		
		
		if ( ! $options['use_ca_patch'] )
			$GLOBALS['ithemes-updater-settings']->disable_ssl_ca_patch();
		
		$GLOBALS['ithemes-updater-settings']->update_options( $options );
		
		
		if ( is_wp_error( $response ) )
			return $response;
		
		
		$body = json_decode( $response['body'], true );
		
		if ( ! empty( $body['error'] ) )
			return new WP_Error( $body['error']['type'], sprintf( __( 'An error occurred when communicating with the iThemes update server: %s (%s)', 'it-l10n-backupbuddy' ), $body['error']['message'], $body['error']['code'] ) );
		
		
		return $body;
	}
	
	private static function get_password_hash( $username, $password ) {
		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		
		$salted_password = $password . $username . get_bloginfo( 'url' ) . $GLOBALS['wp_version'];
		$salted_password = substr( $salted_password, 0, max( strlen( $password ), 72 ) );
		
		$wp_hasher = new PasswordHash( self::$password_iterations, true );
		
		return $wp_hasher->HashPassword( $salted_password );
	}
}
