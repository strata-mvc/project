<?php

class ITSEC_Privilege_Admin {

	private
		$settings,
		$core,
		$module_path;

	function run( $core ) {

		$this->core        = $core;
		$this->settings    = get_site_option( 'itsec_privilege' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'itsec_add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init' ) ); //initialize admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); //enqueue scripts for admin page

		//manually save options on multisite
		if ( is_multisite() ) {
			add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init_multisite' ) ); //save multisite options
		}

	}

	/**
	 * Add prililege escalation admin Javascript
	 *
	 * @since 1.11
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && ( strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_pro' ) !== false ) ) {
			wp_enqueue_script( 'itsec_privilege_js', $this->module_path . 'js/admin-privilege.js', array( 'jquery' ), $itsec_globals['plugin_build'] );
		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * @since 1.11
	 *
	 * @return void
	 */
	public function itsec_add_admin_meta_boxes() {

		$id    = 'privilege_options';
		$title = __( 'Privilege Escalation', 'it-l10n-ithemes-security-pro' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_privilege_settings' ),
			'security_page_toplevel_page_itsec_pro',
			'advanced',
			'core'
		);

		$this->core->add_pro_toc_item(
			array(
				'id'    => $id,
				'title' => $title,
			)
		);

	}

	/**
	 * Execute admin initializations
	 *
	 * @since 1.11
	 *
	 * @return void
	 */
	public function itsec_admin_init() {

		//Add Settings sections
		add_settings_section(
			'privilege-enabled',
			__( 'Enable Privilege Escalation', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		//Add Settings Fields
		add_settings_field(
			'itsec_privilege[enabled]',
			__( 'Enable Privilege Escalation', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_enabled' ),
			'security_page_toplevel_page_itsec_pro',
			'privilege-enabled'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_pro',
			'itsec_privilege',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Prepare and save options in network settings
	 *
	 * @since 1.11
	 *
	 * @return void
	 */
	public function itsec_admin_init_multisite() {

		if ( isset( $_POST['itsec_privilege'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_pro-options' ) ) {
				die( __( 'Security error!', 'it-l10n-ithemes-security-pro' ) );
			}

			update_site_option( 'itsec_privilege', $_POST['itsec_privilege'] ); //we must manually save network options

		}

	}

	/**
	 * Render the settings metabox
	 *
	 * @since 1.11
	 *
	 * @return void
	 */
	public function metabox_privilege_settings() {

		echo '<p>';
		echo __( 'Enabling this feature will allow administrators to temporarily grant extra access to a user of the site for a specified period of time. For example, a contractor can be granted developer access to the site for 24 hours after which his or her status would be automatically revoked.', 'it-l10n-ithemes-security-pro' );
		echo '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'privilege-enabled', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_pro' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-ithemes-security-pro' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @since 1.11
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
	 * echos Enable Privilege Escalation Field
	 *
	 * @since 1.11
	 *
	 * @return void
	 */
	public function settings_field_enabled() {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		echo '<input type="checkbox" id="itsec_privilege_enabled" name="itsec_privilege[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		echo '<label for="itsec_privilege_enabled"> ' . __( 'Enable privilege escalation.', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

}