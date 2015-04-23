<?php

class ITSEC_Online_Files_Admin {

	private
		$core,
		$settings;

	function run( $core ) {

		$this->core     = $core;
		$this->settings = get_site_option( 'itsec_online_files' );

		add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init' ) ); //initialize admin area
		add_filter( 'itsec_add_dashboard_status', array( $this, 'itsec_add_dashboard_status' ) ); //add information for plugin status
		add_filter( 'itsec_tracking_vars', array( $this, 'itsec_tracking_vars' ) );

		//manually save options on multisite
		if ( is_multisite() ) {
			add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init_multisite' ) ); //save multisite options
		}

	}

	/**
	 * Sets the status in the plugin dashboard
	 *
	 * @since 1.10
	 *
	 * @param array $statuses array of dashboard statuses
	 *
	 * @return array array of dashboard statuses
	 */
	public function itsec_add_dashboard_status( $statuses ) {

		if ( $this->settings['enabled'] === true ) {

			$status_array = 'safe-medium';
			$status       = array(
				'text' => __( 'Your site will compare detected file changes with WordPress.org.', 'it-l10n-ithemes-security-pro' ),
				'link' => '#itsec_file_change_settings',
			);

		} else {

			$status_array = 'medium';
			$status       = array(
				'text' => __( 'Your site will not compare detected file changes with WordPress.org.', 'it-l10n-ithemes-security-pro' ),
				'link' => '#itsec_file_change_enabled',
			);

		}

		array_push( $statuses[ $status_array ], $status );

		return $statuses;

	}

	/**
	 * Execute admin initializations
	 *
	 * @since 1.10
	 *
	 * @return void
	 */
	public function itsec_admin_init() {

		//File Change Detection Fields
		add_settings_field(
			'itsec_online_files[enabled]',
			__( 'Compare Files Online', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_enabled' ),
			'security_page_toplevel_page_itsec_settings',
			'file_change-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_online_files',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Prepare and save options in network settings
	 *
	 * @since 1.10
	 *
	 * @return void
	 */
	public function itsec_admin_init_multisite() {

		if ( isset( $_POST['itsec_online_files'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-ithemes-security-pro' ) );
			}

			update_site_option( 'itsec_online_files', $_POST['itsec_online_files'] ); //we must manually save network options

		}

	}

	/**
	 * Adds fields that will be tracked for Google Analytics
	 *
	 * @since 1.10
	 *
	 * @param array $vars tracking vars
	 *
	 * @return array tracking vars
	 */
	public function itsec_tracking_vars( $vars ) {

		$vars['itsec_online_files'] = array(
			'enabled' => '0:b',
		);

		return $vars;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @since 1.10
	 *
	 * @param  Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		$input['enabled'] = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );

		if ( is_multisite() ) {

			$this->core->show_network_admin_notice( false );

			$this->settings = $input;

		}

		return $input;

	}

	/**
	 * echos Enable online file change detection Field
	 *
	 * @since 1.10
	 *
	 * @return void
	 */
	public function settings_field_enabled() {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		echo '<input type="checkbox" id="itsec_online_files_enabled" name="itsec_online_files[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		echo '<label for="itsec_online_files_enabled"> ' . __( 'Enable Online file comparison', 'it-l10n-ithemes-security-pro' ) . '</label>';
		echo '<p class="description"> ' . __( 'When any WordPress core file or file in an iThemes plugin or theme has been changed on your system, this feature will compare it with the version on WordPress.org or iThemes (as appropriate) to determine if the change was malicious. Currently this feature only works with WordPress core files and plugins and themes by iThemes (plugins and themes from other sources will be added as available).', 'it-l10n-ithemes-security-pro' ) . '</p>';

	}

}