<?php

/**
 * Online File Scan Execution
 *
 * Handles all online file scan execution once the feature has been
 * enabled by the user.
 *
 * @since   1.10.0
 *
 * @package iThemes_Security
 */
class ITSEC_Online_Files {

	function run() {

		add_action( 'itsec_process_added_files', array( $this, 'itsec_process_added_files' ) );
		add_action( 'itsec_process_changed_file', array( $this, 'itsec_process_changed_file' ), 10, 3 );

	}

	/**
	 * Retrieves core hashes from remote API.
	 *
	 * Retreives all core file hashes from the WordPress.org API.
	 *
	 * @since  1.14.0
	 *
	 * @access private
	 *
	 * @return array|mixed Array of core hash files or false if not available.
	 */
	private function get_core_hashes() {

		global $wp_version;

		$siteurl   = parse_url( get_site_url() );
		$directory = isset( $siteurl['path'] ) ? trailingslashit( substr( $siteurl['path'], 1 ) ) : '';

		$core_files = get_site_transient( 'itsec_online_files_core_hashes' );

		if ( false == $core_files ) {

			$raw_files = wp_remote_get( 'https://api.wordpress.org/core/checksums/1.0/?version=' . sanitize_text_field( $wp_version ) . '&locale=' . sanitize_text_field( get_locale() ) );

			if ( is_array( $raw_files ) && isset( $raw_files['body'] ) ) {

				$decoded_raw_files = json_decode( $raw_files['body'], true );

				if ( isset( $decoded_raw_files['checksums'] ) && false !== $decoded_raw_files['checksums'] ) {

					set_site_transient( 'itsec_online_files_core_hashes', $decoded_raw_files['checksums'], 604800 ); //only update once a week

					$hashes = array();

					foreach ( $decoded_raw_files['checksums'] as $file => $hash ) {
						$hashes[$directory . $file] = $hash;
					}

					return $hashes;

				} else {

					set_site_transient( 'itsec_online_files_core_hashes', null, 3600 );

					return false; //couldn't get the remote files so just return false

				}

			}

		}

		return $core_files;

	}

	/**
	 * Retrieves hashes for available plugins
	 *
	 * Retrieves all file hashes for available plugins from the appropriate repo
	 *
	 * @since  1.14.0
	 *
	 * @access private
	 *
	 * @return array Array of available plugin file hashes
	 */
	private function get_plugin_hashes() {

		global $itsec_globals;

		if ( ! is_callable( 'get_plugins' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( ! is_callable( 'get_plugins' ) ) {
			return false;
		}

		$plugin_dir    = parse_url( plugin_dir_url( $itsec_globals['plugin_file'] ) );
		$plugin_dir    = trailingslashit( substr( dirname( $plugin_dir['path'] ), 1 ) );
		$plugins       = get_plugins();
		$valid_plugins = array();

		foreach ( $plugins as $plugin => $data ) {

			$plugin_parts = explode( '/', $plugin );
			$plugin_slug  = $plugin_parts[0];

			$plugin_data = get_site_transient( 'itsec_plugin_hashes_' . $plugin_slug );

			if ( false == $plugin_data ) {

				$plugin_headers = array(
					'Version'         => 'Version',
					'iThemes Package' => 'iThemes Package',
				);

				$plugin_file = trailingslashit( WP_PLUGIN_DIR ) . $plugin;

				$plugin_data = get_file_data( $plugin_file, $plugin_headers );

				if ( isset( $plugin_data['iThemes Package'] ) && 1 < strlen( trim( $plugin_data['iThemes Package'] ) ) ) {

					$hash_url = 'https://s3.amazonaws.com/package-hash.ithemes.com/' . $plugin_slug . '/' . $plugin_data['Version'] . '.json';

					$raw_hash = wp_remote_get( $hash_url );

					if ( is_array( $raw_hash ) && isset( $raw_hash['response'] ) && isset( $raw_hash['response']['code'] ) && '200' == $raw_hash['response']['code'] && isset( $raw_hash['body'] ) ) {

						$plugin_hashes = json_decode( $raw_hash['body'] );

						foreach ( $plugin_hashes as $file => $hash ) {
							$plugin_data['h'][$plugin_dir . $file] = $hash;
						}

					}

				}

				unset( $plugin_data['iThemes Package'] ); //don't save this as we don't need it

				set_site_transient( 'itsec_plugin_hashes_' . $plugin_slug, $plugin_data, 604800 );

			}

			if ( is_array( $plugin_data ) && isset( $plugin_data['h'] ) ) {
				$valid_plugins[$plugin] = $plugin_data;
			}

		}

		return $valid_plugins;

	}

	/**
	 * Returns all file hashes.
	 *
	 * Returns all available remote file hashes
	 *
	 * @since  1.14.0
	 *
	 * @return array|mixed Array of core hash files or false if not available.
	 */
	public function get_remote_hashes() {

		$remote_hashes = array();

		$plugins = $this->get_plugin_hashes();

		if ( is_array( $plugins ) ) {

			foreach ( $plugins as $plugin ) {
				$remote_hashes = array_merge( $remote_hashes, $plugin['h'] );
			}

		}

		$themes = $this->get_theme_hashes();

		if ( is_array( $themes ) ) {

			foreach ( $themes as $theme ) {
				$remote_hashes = array_merge( $remote_hashes, $theme['h'] );
			}

		}

		$core_hashes = $this->get_core_hashes();

		if ( is_array( $core_hashes ) ) {
			$remote_hashes = array_merge( $remote_hashes, $core_hashes );
		}

		return $remote_hashes;

	}

	/**
	 * Retrieves hashes for available themes
	 *
	 * Retreives all file hashes for available themes from the appropriate repo
	 *
	 * @since  1.14.0
	 *
	 * @access private
	 *
	 * @return array Array of available theme file hashes
	 */
	private function get_theme_hashes() {

		$themes_dir   = parse_url( get_theme_root_uri() );
		$themes_dir   = trailingslashit( substr( $themes_dir['path'], 1 ) );
		$themes       = wp_get_themes( null );
		$valid_themes = array();

		foreach ( $themes as $theme => $data ) {

			delete_site_transient( 'itsec_theme_hashes_' . $theme );
			$theme_data = get_site_transient( 'itsec_theme_hashes_' . $theme );

			if ( false == $theme_data ) {

				$theme_headers = array(
					'Version'         => 'Version',
					'iThemes Package' => 'iThemes Package',
				);

				$theme_file = trailingslashit( $data->theme_root ) . $theme . '/style.css';

				$theme_data = get_file_data( $theme_file, $theme_headers );

				if ( isset( $theme_data['iThemes Package'] ) && 1 < strlen( trim( $theme_data['iThemes Package'] ) ) ) {

					$hash_url = 'https://s3.amazonaws.com/package-hash.ithemes.com/' . $theme . '/' . $theme_data['Version'] . '.json';

					$raw_hash = wp_remote_get( $hash_url );

					if ( is_array( $raw_hash ) && isset( $raw_hash['response'] ) && isset( $raw_hash['response']['code'] ) && '200' == $raw_hash['response']['code'] && isset( $raw_hash['body'] ) ) {

						$theme_hashes = json_decode( $raw_hash['body'] );

						foreach ( $theme_hashes as $file => $hash ) {
							$theme_data['h'][$themes_dir . $file] = $hash;
						}

					}

				}

				unset( $theme_data['iThemes Package'] ); //don't save this as we don't need it

				set_site_transient( 'itsec_theme_hashes_' . $theme, $theme_data, 604800 );

			}

			if ( is_array( $theme_data ) && isset( $theme_data['h'] ) ) {
				$valid_themes[$theme] = $theme_data;
			}

		}

		return $valid_themes;

	}

	/**
	 * Compare files added with remote repository.
	 *
	 * Looks at all new files found by the local file scan and compares them to
	 * the appropriate remove hash if available.
	 *
	 * @since 1.10.0
	 *
	 * @param array $files_added Array of files added since last local check
	 *
	 * @return mixed false or array of files confirmed changed
	 */
	public function itsec_process_added_files( $files_added ) {

		$hashes = $this->get_remote_hashes();

		if ( false === $hashes ) {
			return $files_added;
		}

		foreach ( $files_added as $file => $attr ) {

			if ( isset( $hashes[$file] ) && isset( $file['h'] ) && $file['h'] === $hashes[$file] ) {
				unset( $files_added[$file] );
			}

		}

		return ( $files_added );

	}

	/**
	 * Compare a file that has been marked as changed since the last local scan.
	 *
	 * Looks at all the changed files found by the local scan and compares them
	 * to their remote hashes if they're available.
	 *
	 * @since 1.10.0
	 *
	 * @param bool   $changed whether the file has been changed or not
	 * @param string $file    The name of the file to check
	 * @param string $hash    the md5 to check
	 *
	 * @return bool whether a remote difference is detected or false
	 */
	public function itsec_process_changed_file( $changed, $file, $hash ) {

		$hashes = $this->get_remote_hashes();

		if ( false === $hashes ) {
			return $changed;
		}

		if ( isset( $hashes[$file] ) && $hash === $hashes[$file] ) {
			$changed = false;
		}

		return $changed;

	}

}
