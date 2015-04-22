<?php

class ITSEC_Password_Admin {

	private
		$settings,
		$core,
		$module_path;

	function run( $core ) {

		$this->core        = $core;
		$this->settings    = get_site_option( 'itsec_password' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'itsec_add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init' ) ); //initialize admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); //enqueue scripts for admin page

		add_filter( 'itsec_one_click_settings', array( $this, 'itsec_one_click_settings' ) ); //Add password generator to 1-click for admins only

		//manually save options on multisite
		if ( is_multisite() ) {
			add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init_multisite' ) ); //save multisite options
		}

	}

	/**
	 * Add malware scheduling admin Javascript
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && ( strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_pro' ) !== false ) ) {

			wp_enqueue_script( 'itsec_password_js', $this->module_path . 'js/admin-password.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function itsec_add_admin_meta_boxes() {

		$id    = 'password_options';
		$title = __( 'WordPress Passwords', 'it-l10n-ithemes-security-pro' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_malware_scheduling_settings' ),
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
	 * @since 1.6
	 *
	 * @return void
	 */
	public function itsec_admin_init() {

		//Add Settings sections
		add_settings_section(
			'password-enabled',
			__( 'Enable WordPress Password Enforcement', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		add_settings_section(
			'password-settings',
			__( 'WordPress Password Settings', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		add_settings_section(
			'password-settings-2',
			__( 'WordPress Password Settings', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		add_settings_section(
			'password-settings-3',
			__( 'WordPress Password Settings', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		add_settings_section(
			'password-settings-4',
			__( 'WordPress Password Settings', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		//Add Settings Fields
		add_settings_field(
			'itsec_password[enabled]',
			__( 'Enable WordPress Password Enforcement', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_enabled' ),
			'security_page_toplevel_page_itsec_pro',
			'password-enabled'
		);

		add_settings_field(
			'itsec_password[generate]',
			__( 'Add Strong Password Generation to User Profile Page', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_generate' ),
			'security_page_toplevel_page_itsec_pro',
			'password-settings'
		);

		add_settings_field(
			'itsec_password[generate_role]',
			__( 'Select Roles Allowed to Generate Strong Passwords', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_generate_role' ),
			'security_page_toplevel_page_itsec_pro',
			'password-settings-2'
		);

		add_settings_field(
			'itsec_password[generate_length]',
			__( 'Base Length of Generated Password', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_generate_length' ),
			'security_page_toplevel_page_itsec_pro',
			'password-settings-2'
		);

		add_settings_field(
			'itsec_password[expire]',
			__( 'Enable Password Expiration', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_expire' ),
			'security_page_toplevel_page_itsec_pro',
			'password-settings-3'
		);

		add_settings_field(
			'itsec_password[expire_role]',
			__( 'Select Minimum Role for Password Expiration', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_expire_role' ),
			'security_page_toplevel_page_itsec_pro',
			'password-settings-4'
		);

		add_settings_field(
			'itsec_password[expire_force]',
			__( 'Force Password Change on Next Login', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_expire_force' ),
			'security_page_toplevel_page_itsec_pro',
			'password-settings-4'
		);

		add_settings_field(
			'itsec_password[expire_max]',
			__( 'Maximum Password Age', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_expire_max' ),
			'security_page_toplevel_page_itsec_pro',
			'password-settings-4'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_pro',
			'itsec_password',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Prepare and save options in network settings
	 *
	 * @return void
	 */
	public function itsec_admin_init_multisite() {

		if ( isset( $_POST['itsec_password'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_pro-options' ) ) {
				die( __( 'Security error!', 'it-l10n-ithemes-security-pro' ) );
			}

			update_site_option( 'itsec_password', $_POST['itsec_password'] ); //we must manually save network options

		}

	}

	/**
	 * Register one-click settings
	 *
	 * @since 1.8
	 *
	 * @param array $one_click_settings array of one-click settings
	 *
	 * @return array array of one-click settings
	 */
	public function itsec_one_click_settings( $one_click_settings ) {

		$one_click_settings['itsec_password'][] = array(
			'option' => 'generator',
			'value'  => 1,
		);

		return $one_click_settings;

	}

	/**
	 * Render the settings metabox
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_malware_scheduling_settings() {

		echo '<p>';
		echo __( 'Use the options below to strengthen the passwords users use to log in to your site. You can enforce password expiration, add a strong passwords generator to user profiles and more.', 'it-l10n-ithemes-security-pro' );
		echo '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'password-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'password-settings', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'password-settings-2', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'password-settings-3', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'password-settings-4', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_pro' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-ithemes-security-pro' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @since 1.6
	 *
	 * @param  Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		global $itsec_globals;

		$input['enabled']         = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );
		$input['generate']        = ( isset( $input['generate'] ) && intval( $input['generate'] == 1 ) ? true : false );
		$input['expire']          = ( isset( $input['expire'] ) && intval( $input['expire'] == 1 ) ? true : false );
		$input['expire_max']      = isset( $input['expire_max'] ) ? absint( $input['expire_max'] ) : 120;
		$input['generate_length'] = isset( $input['generate_length'] ) ? absint( $input['generate_length'] ) : 50;

		if ( isset( $input['generate_role'] ) && ctype_alpha( wp_strip_all_tags( $input['generate_role'] ) ) ) {
			$input['generate_role'] = wp_strip_all_tags( $input['generate_role'] );
		}

		if ( isset( $input['expire_role'] ) && ctype_alpha( wp_strip_all_tags( $input['expire_role'] ) ) ) {
			$input['expire_role'] = wp_strip_all_tags( $input['expire_role'] );
		}

		//Handle forced change
		if ( isset( $input['expire_force'] ) ) {

			delete_metadata( 'user', null, 'itsec_last_password_change', null, true ); //delete existing last password change

			$input['expire_force'] = $itsec_globals['current_time_gmt'];

		} elseif ( isset( $this->settings['expire_force'] ) ) { //don't change it if we've set it before

			$input['expire_force'] = $this->settings['expire_force'];

		} else {

			$input['expire_force'] = false; //They're not forcing a password reset at this time.

		}

		if ( is_multisite() ) {

			$this->core->show_network_admin_notice( false );

			$this->settings = $input;

		}

		return $input;

	}

	/**
	 * echos Enable WordPress Password Enabled Field
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function settings_field_enabled() {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		echo '<input type="checkbox" id="itsec_password_enabled" name="itsec_password[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		echo '<label for="itsec_password_enabled"> ' . __( 'Enable WordPress Password Enforcement', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos Enable password expiration Field
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function settings_field_expire() {

		if ( isset( $this->settings['expire'] ) && $this->settings['expire'] === true ) {
			$expire = 1;
		} else {
			$expire = 0;
		}

		echo '<input type="checkbox" id="itsec_password_expire" name="itsec_password[expire]" value="1" ' . checked( 1, $expire, false ) . '/>';
		echo '<label for="itsec_password_expire"> ' . __( 'Enable password expiration', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos Force password change on next login field
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function settings_field_expire_force() {

		echo '<input type="checkbox" id="itsec_password_expire_force" name="itsec_password[expire_force]" value="1" />';
		echo '<label for="itsec_password_expire_force"> ' . __( 'Force password change', 'it-l10n-ithemes-security-pro' ) . '</label>';
		echo '<p class="description">' . __( 'Checking this box will force all users to change their password upon their next login.', 'it-l10n-ithemes-security-pro' ) . '</p>';

	}

	/**
	 * echos Backup Interval Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function settings_field_expire_max() {

		if ( isset( $this->settings['expire_max'] ) ) {
			$expire_max = absint( $this->settings['expire_max'] );
		} else {
			$expire_max = 120;
		}

		echo '<input class="small-text" name="itsec_password[expire_max]" id="itsec_password_expire_max" value="' . $expire_max . '" type="text"> ';
		echo '<label for="itsec_password_expire_max"> ' . __( 'Days', 'it-l10n-ithemes-security-pro' ) . '</label>';
		echo '<p class="description"> ' . __( 'The maximum number of days a password may be kept before it is expired.', 'it-l10n-ithemes-security-pro' ) . '</p>';

	}

	/**
	 * echos Enable WordPress Password Enabled Field
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function settings_field_expire_role() {

		if ( isset( $this->settings['expire_role'] ) ) {
			$expire_role = $this->settings['expire_role'];
		} else {
			$expire_role = 'administrator';
		}

		echo '<select name="itsec_password[expire_role]" id="itsec_password_expire_role">';
		echo '<option value="administrator" ' . selected( $expire_role, 'administrator', false ) . '>' . translate_user_role( 'Administrator' ) . '</option>';
		echo '<option value="editor" ' . selected( $expire_role, 'editor', false ) . '>' . translate_user_role( 'Editor' ) . '</option>';
		echo '<option value="author" ' . selected( $expire_role, 'author', false ) . '>' . translate_user_role( 'Author' ) . '</option>';
		echo '<option value="contributor" ' . selected( $expire_role, 'contributor', false ) . '>' . translate_user_role( 'Contributor' ) . '</option>';
		echo '<option value="subscriber" ' . selected( $expire_role, 'subscriber', false ) . '>' . translate_user_role( 'Subscriber' ) . '</option>';
		echo '</select><br>';
		echo '<label for="itsec_password_expire_role"> ' . __( 'Minimum role at which password expiration is enforced.' ) . '</label>';
		echo '<p class="description"> ' . __( 'We suggest enabling this setting for all users, but it may lead to users forgetting their passwords. The minimum role option above allows you to select the lowest user role to apply strong password generation.', 'it-l10n-ithemes-security-pro' ) . '</p>';
		echo '<p class="description"> ' . __( 'For more information on WordPress roles and capabilities please see', 'it-l10n-ithemes-security-pro' ) . ' <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">http://codex.wordpress.org/Roles_and_Capabilities</a>.</p>';

	}

	/**
	 * echos Enable strong password generation Field
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function settings_field_generate() {

		if ( isset( $this->settings['generate'] ) && $this->settings['generate'] === true ) {
			$generate = 1;
		} else {
			$generate = 0;
		}

		echo '<input type="checkbox" id="itsec_password_generate" name="itsec_password[generate]" value="1" ' . checked( 1, $generate, false ) . '/>';
		echo '<label for="itsec_password_generate"> ' . __( 'Allow Strong Password Generation', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos Enable strong password generation length feature Field
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function settings_field_generate_length() {

		if ( isset( $this->settings['generate_length'] ) ) {
			$generate_length = absint( $this->settings['generate_length'] );
		} else {
			$generate_length = 50;
		}

		echo '<input class="small-text" name="itsec_password[generate_length]" id="itsec_password_generate_length" value="' . $generate_length . '" type="text"> ';
		echo '<label for="itsec_password_generate_length"> ' . __( 'Characters', 'it-l10n-ithemes-security-pro' ) . '</label>';
		echo '<p class="description"> ' . __( 'The base number of characters of a generated password. Note the plugin will randomly generate a password starting from this length and up to 10 characters longer.', 'it-l10n-ithemes-security-pro' ) . ' <strong>' . __( 'In addition lowering the password length to less that 50 can result in a generated password that will not register as "strong" on the WordPress password strength meter.', 'it-l10n-ithemes-security-pro' ) . '</strong></p>';

	}

	/**
	 * echos Enable WordPress Password Enabled Field
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function settings_field_generate_role() {

		if ( isset( $this->settings['generate_role'] ) ) {
			$generate_role = $this->settings['generate_role'];
		} else {
			$generate_role = 'administrator';
		}

		echo '<select name="itsec_password[generate_role]" id="itsec_password_generate_role">';
		echo '<option value="administrator" ' . selected( $generate_role, 'administrator', false ) . '>' . translate_user_role( 'Administrator' ) . '</option>';
		echo '<option value="editor" ' . selected( $generate_role, 'editor', false ) . '>' . translate_user_role( 'Editor' ) . '</option>';
		echo '<option value="author" ' . selected( $generate_role, 'author', false ) . '>' . translate_user_role( 'Author' ) . '</option>';
		echo '<option value="contributor" ' . selected( $generate_role, 'contributor', false ) . '>' . translate_user_role( 'Contributor' ) . '</option>';
		echo '<option value="subscriber" ' . selected( $generate_role, 'subscriber', false ) . '>' . translate_user_role( 'Subscriber' ) . '</option>';
		echo '</select><br>';
		echo '<label for="itsec_password_generate_role"> ' . __( 'Minimum role at which a user can generate a strong password.' ) . '</label>';

		echo '<p class="description"> ' . __( 'We suggest enabling this for all users, but it may lead to users forgetting their passwords. The minimum role option above allows you to select the lowest role to apply password expiration.', 'it-l10n-ithemes-security-pro' ) . '</p>';
		echo '<p class="description"> ' . __( 'For more information on WordPress roles and capabilities please see', 'it-l10n-ithemes-security-pro' ) . ' <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">http://codex.wordpress.org/Roles_and_Capabilities</a>.</p>';

	}

}