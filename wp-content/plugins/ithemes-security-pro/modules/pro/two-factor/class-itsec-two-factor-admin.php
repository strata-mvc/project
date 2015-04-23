<?php

/**
 * Two Factor Administrative Screens
 *
 * Sets up all administrative functions for the two-factor authentication feature
 * including fields, sanitation and all other privileged functions.
 *
 * @since   1.2.0
 *
 * @package iThemes_Security
 */
class ITSEC_Two_Factor_Admin {

	/**
	 * The module's saved options
	 *
	 * @since  1.2.0
	 * @access private
	 * @var array
	 */
	private $settings;

	/**
	 * The core plugin class utilized in order to set up admin and other screens
	 *
	 * @since  1.2.0
	 * @access private
	 * @var ITSEC_Core
	 */
	private $core;

	/**
	 * The absolute web patch to the module's files
	 *
	 * @since  1.2.0
	 * @access private
	 * @var string
	 */
	private $module_path;

	/**
	 * Setup the module's administrative functionality
	 *
	 * Loads the two-factor module's priviledged functionality including
	 * settings fields.
	 *
	 * @since 1.2.0
	 *
	 * @param ITSEC_Core $core The core plugin instance
	 *
	 * @return void
	 */
	function run( $core ) {

		$this->core        = $core;
		$this->settings    = get_site_option( 'itsec_two_factor' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); //enqueue scripts for admin page
		add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init' ) ); //initialize admin area
		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'itsec_add_admin_meta_boxes' ) ); //add meta boxes to admin page

		add_filter( 'itsec_add_dashboard_status', array( $this, 'itsec_add_dashboard_status' ) ); //add information for plugin status
		add_filter( 'itsec_tracking_vars', array( $this, 'itsec_tracking_vars' ) );

		//manually save options on multisite
		if ( is_multisite() ) {
			add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init_multisite' ) ); //save multisite options
		}

	}

	/**
	 * Add two-factor Admin Javascript
	 *
	 * Enqueues files used in the admin area for the file change module
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && false !== strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_pro' ) ) {

			wp_enqueue_script( 'itsec_two_factor_js', $this->module_path . 'js/admin-two-factor.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * Adds the module's meta settings box to the settings page and
	 * registers the added box in the page's table of contents.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function itsec_add_admin_meta_boxes() {

		$id    = 'two_factor_options';
		$title = __( 'Two Factor', 'it-l10n-ithemes-security-pro' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_two_factor_settings' ),
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
	 * Sets the status in the plugin dashboard
	 *
	 * Sets a low or high (depending on scheduled or not) priority item for the module's functionality
	 * in the plugin dashboard.
	 *
	 * @since 1.2.0
	 *
	 * @param array $statuses array of existing plugin dashboard statuses
	 *
	 * @return array statuses
	 */
	public function itsec_add_dashboard_status( $statuses ) {

		if ( true === $this->settings['enabled'] && 'subscriber' == $this->settings['roll'] ) {

			$status_array = 'safe-high';
			$status       = array( 'text' => __( 'You are allowing two-factor authentication for all users.', 'it-l10n-ithemes-security-pro' ), 'link' => '#itsec_two_factor_enabled', 'pro' => true, );

		} elseif ( true === $this->settings['enabled'] ) {

			$status_array = 'low';
			$status       = array( 'text' => __( 'You are allowing two-factor authentication, but not for all users.', 'it-l10n-ithemes-security-pro' ), 'link' => '#itsec_two_factor_enabled', 'pro' => true, );

		} else {

			$status_array = 'high';
			$status       = array( 'text' => __( 'You are not allowing two-factor authentication for any users.', 'it-l10n-ithemes-security-pro' ), 'link' => '#itsec_two_factor_enabled', 'pro' => true, );

		}

		array_push( $statuses[ $status_array ], $status );

		return $statuses;

	}

	/**
	 * Execute admin initializations
	 *
	 * Calls the dashboard warning method and sets up all module settings fields and
	 * sections.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function itsec_admin_init() {

		//Add Settings sections
		add_settings_section(
			'two_factor-enabled',
			__( 'Enable Two Factor Authentication', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		add_settings_section(
			'two_factor-settings',
			__( 'Two Factor Authentication', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		//Strong Passwords Fields
		add_settings_field(
			'itsec_two_factor[enabled]',
			__( 'Enable Two Factor', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_enabled' ),
			'security_page_toplevel_page_itsec_pro',
			'two_factor-enabled'
		);

		add_settings_field(
			'itsec_two_factor[roll]',
			__( 'Lowest Role', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_role' ),
			'security_page_toplevel_page_itsec_pro',
			'two_factor-settings'
		);

		add_settings_field(
			'itsec_two_factor[offset]',
			__( 'Server Time Offset', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_offset' ),
			'security_page_toplevel_page_itsec_pro',
			'two_factor-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_pro',
			'itsec_two_factor',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Prepare and save options in network settings
	 *
	 * Saves the options in a multi-site network where data sanitization and processing is not
	 * called automatically on form submission.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function itsec_admin_init_multisite() {

		if ( isset( $_POST['itsec_two_factor'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_pro-options' ) ) {
				die( __( 'Security error!', 'it-l10n-ithemes-security-pro' ) );
			}

			update_site_option( 'itsec_two_factor', $_POST['itsec_two_factor'] ); //we must manually save network options

		}

	}

	/**
	 * Adds fields that will be tracked for Google Analytics
	 *
	 * Registers all settings in the module that will be tracked on change by
	 * Google Analytics if "allow tracking" is enabled.
	 *
	 * @since 1.2.0
	 *
	 * @param array $vars tracking vars
	 *
	 * @return array tracking vars
	 */
	public function itsec_tracking_vars( $vars ) {

		$vars['itsec_two_factor'] = array(
			'enabled' => '0:b',
			'roll'    => 'administrator:s',
		);

		return $vars;

	}

	/**
	 * Render the settings metabox
	 *
	 * Displays the contents of the module's settings metabox on the "Pro"
	 * page with all module options.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function metabox_two_factor_settings() {

		global $itsec_globals;

		echo '<p>' . __( 'Allow users to log in with two-factor authentication devices such as Google Authenticator or Authy', 'it-l10n-ithemes-security-pro' ) . '</p>';

		echo sprintf(
			'<div class="itsec-notice-message"><span>%s: </span> %s <strong>%s</strong> %s <a href="%s">%s</a>.</div>',
			__( 'Notice', 'it-l10n-ithemes-security-pro' ),
			__( 'Please verify your server\'s time is correct before enabling this feature. Your server time must be within 30 seconds of your two-factor device for this to be successful. Your server is reporting the current time as', 'it-l10n-ithemes-security-pro' ),
			date( 'g:i:s a', $itsec_globals['current_time'] ),
			__( 'This time was taken when this page was loaded and may be old. You may want to refresh this page to verify the correct time. If the time is incorrect please verify your', 'it-l10n-ithemes-security-pro' ),
			admin_url( 'options-general.php#timezone_string' ),
			__( 'timezone settings on the WordPress settings page', 'it-l10n-ithemes-security-pro' ),
			__( '', 'it-l10n-ithemes-security-pro' )
		);

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'two_factor-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'two_factor-settings', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_pro' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-ithemes-security-pro' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Sanitize and validate input
	 *
	 * Sanitizes and validates module options saved on the settings page or via multisite.
	 *
	 * @since 1.2.0
	 *
	 * @param  Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		//process strong passwords settings
		$input['enabled'] = ( isset( $input['enabled'] ) && 1 === intval( $input['enabled'] ) ? true : false );

		if ( isset( $input['roll'] ) && ctype_alpha( wp_strip_all_tags( $input['roll'] ) ) ) {
			$input['roll'] = wp_strip_all_tags( $input['roll'] );
		}

		$input['offset'] = isset( $input['offset'] ) ? intval( $input['offset'] ) : 1;

		if ( is_multisite() ) {

			$this->core->show_network_admin_notice( false );

			$this->settings = $input;

		}

		return $input;

	}

	/**
	 * echos Enable two factor Field
	 *
	 * Echo's the settings field that determines whether or not the two factor module is enabled.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function settings_field_enabled() {

		if ( isset( $this->settings['enabled'] ) && true === $this->settings['enabled'] ) {

			$enabled = 1;

		} else {

			$enabled = 0;

		}

		echo '<input type="checkbox" id="itsec_two_factor_enabled" name="itsec_two_factor[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		echo '<label for="itsec_two_factor_enabled"> ' . __( 'Enable Two-factor authentication.', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos two-factor offset field
	 *
	 * Echos the settings field that determins the server time offset for servers in which the
	 * time cannot be correctly set for whatever reason.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function settings_field_offset() {

		if ( isset( $this->settings['offset'] ) ) {

			$offset = $this->settings['offset'];

		} else {

			$offset = 1;

		}

		echo '<select name="itsec_two_factor[offset]" id="itsec_two_factor_offset">';
		echo '<option value="1" ' . selected( $offset, 1, false ) . '>30</option>';
		echo '<option value="2" ' . selected( $offset, 2, false ) . '>60</option>';
		echo '<option value="3" ' . selected( $offset, 3, false ) . '>90</option>';
		echo '<option value="4" ' . selected( $offset, 4, false ) . '>120</option>';
		echo '<option value="5" ' . selected( $offset, 5, false ) . '>150</option>';
		echo '<option value="6" ' . selected( $offset, 6, false ) . '>180</option>';
		echo '<option value="7" ' . selected( $offset, 7, false ) . '>210</option>';
		echo '<option value="8" ' . selected( $offset, 8, false ) . '>240</option>';
		echo '<option value="9" ' . selected( $offset, 9, false ) . '>270</option>';
		echo '<option value="10" ' . selected( $offset, 10, false ) . '>300</option>';
		echo '</select><br>';
		echo '<label for="itsec_two_factor_offset"> ' . __( 'Set the server time allowance.' ) . '</label>';

		echo '<p class="description"> ' . __( 'Two-factor authentication is completely dependant on the correct time being set on your server. Normally your server time must be correct within 30 seconds. If your server time is off by up to 5 minutes you can still make it work however by adjusting the offset field which will compensate for incorrect time in 30 second increments. Do not adjust this settings unless you have to.', 'it-l10n-ithemes-security-pro' ) . '</p>';

	}

	/**
	 * echos two factor Role Field.
	 *
	 * Echos the field that determines the minimum role a use must have in order to use two-factor
	 * authentication.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function settings_field_role() {

		if ( isset( $this->settings['roll'] ) ) {

			$roll = $this->settings['roll'];

		} else {

			$roll = 'administrator';

		}

		echo '<select name="itsec_two_factor[roll]" id="itsec_two_factor_roll">';
		echo '<option value="administrator" ' . selected( $roll, 'administrator', false ) . '>' . translate_user_role( 'Administrator' ) . '</option>';
		echo '<option value="editor" ' . selected( $roll, 'editor', false ) . '>' . translate_user_role( 'Editor' ) . '</option>';
		echo '<option value="author" ' . selected( $roll, 'author', false ) . '>' . translate_user_role( 'Author' ) . '</option>';
		echo '<option value="contributor" ' . selected( $roll, 'contributor', false ) . '>' . translate_user_role( 'Contributor' ) . '</option>';
		echo '<option value="subscriber" ' . selected( $roll, 'subscriber', false ) . '>' . translate_user_role( 'Subscriber' ) . '</option>';
		echo '</select><br>';
		echo '<label for="itsec_two_factor_roll"> ' . __( 'Minimum role at which a user can use two-factor authentication.' ) . '</label>';

		echo '<p class="description"> ' . __( 'For more information on WordPress roles and capabilities please see', 'it-l10n-ithemes-security-pro' ) . ' <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">http://codex.wordpress.org/Roles_and_Capabilities</a>.</p>';
		echo '<p class="warningtext description">' . __( 'Warning: If your site invites public registrations setting the role too low may annoy your members.', 'it-l10n-ithemes-security-pro' ) . '</p>';
		echo '<p class="warningtext description"><strong>' . __( 'Note', 'it-l10n-ithemes-security-pro' ) . '</strong>:' . __( 'Two factor will need to be enabled for each account by the account owner. To override two factor authentication to restore access to an account a site admin can edit the user\'s profile.', 'it-l10n-ithemes-security-pro' ) . '</p>';

	}

}