<?php

class ITSEC_User_Logging_Admin {

	private
		$settings,
		$core,
		$module_path;

	function run( $core ) {

		$this->core        = $core;
		$this->settings    = get_site_option( 'itsec_user_logging' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'itsec_add_admin_meta_boxes', array(
			$this, 'add_admin_meta_boxes'
		) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) ); //enqueue scripts for admin page
		add_filter( 'itsec_add_dashboard_status', array(
			$this, 'dashboard_status'
		) ); //add information for plugin status
		add_filter( 'itsec_tracking_vars', array( $this, 'tracking_vars' ) );
		add_filter( 'itsec_logger_displays', array( $this, 'register_logger_displays' ) ); //adds logs metaboxes

		//manually save options on multisite
		if ( is_multisite() ) {
			add_action( 'itsec_admin_init', array( $this, 'save_network_options' ) ); //save multisite options
		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * @return void
	 */
	public function add_admin_meta_boxes() {

		$id    = 'user_logging_options';
		$title = __( 'User Logging', 'it-l10n-ithemes-security-pro' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_user_logging_settings' ),
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
	 * Add Away mode Javascript
	 *
	 * @return void
	 */
	public function admin_script() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_pro' ) !== false ) {

			wp_enqueue_script( 'itsec_user_logging_js', $this->module_path . 'js/admin-user-logging.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

		}

	}

	/**
	 * Sets the status in the plugin dashboard
	 *
	 * @since 4.0
	 *
	 * @return array array of statuses
	 */
	public function dashboard_status( $statuses ) {

		if ( $this->settings['enabled'] === true && $this->settings['roll'] == 'subscriber' ) {

			$status_array = 'safe-low';
			$status       = array(
				'text' => __( 'You are logging admin actions for all users.', 'it-l10n-ithemes-security-pro' ),
				'link' => '#itsec_user_logging_enabled', 'pro' => true,
			);

		} elseif ( $this->settings['enabled'] === true ) {

			$status_array = 'low';
			$status       = array(
				'text' => __( 'You are logging admin actions, but not for all users.', 'it-l10n-ithemes-security-pro' ),
				'link' => '#itsec_user_logging_enabled', 'pro' => true,
			);

		} else {

			$status_array = 'low';
			$status       = array(
				'text' => __( 'You are not logging user actions for any users.', 'it-l10n-ithemes-security-pro' ),
				'link' => '#itsec_user_logging_enabled', 'pro' => true,
			);

		}

		array_push( $statuses[$status_array], $status );

		return $statuses;

	}

	/**
	 * Execute admin initializations
	 *
	 * @return void
	 */
	public function initialize_admin() {

		//Add Settings sections
		add_settings_section(
			'user_logging-enabled',
			__( 'Enable User Logging', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		add_settings_section(
			'user_logging-settings',
			__( 'Enable User Logging', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		//Strong Passwords Fields
		add_settings_field(
			'itsec_user_logging[enabled]',
			__( 'Enable User Logging', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'user_logging_enabled' ),
			'security_page_toplevel_page_itsec_pro',
			'user_logging-enabled'
		);

		add_settings_field(
			'itsec_user_logging[roll]',
			__( 'Select Role for User Logging', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'user_logging_role' ),
			'security_page_toplevel_page_itsec_pro',
			'user_logging-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_pro',
			'itsec_user_logging',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function logs_metabox_content() {

		if ( ! class_exists( 'ITSEC_User_Logging_Log' ) ) {
			require( dirname( __FILE__ ) . '/class-itsec-user-logging-log.php' );
		}

		$log_display = new ITSEC_User_Logging_Log();

		$log_display->prepare_items();
		$log_display->display();

	}

	/**
	 * Render the settings metabox
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_user_logging_settings() {

		echo '<p>' . __( 'Log user actions such as login, saving content and others.', 'it-l10n-ithemes-security-pro' ) . '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'user_logging-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'user_logging-settings', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_pro' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-ithemes-security-pro' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Array of metaboxes for the logs screen
	 *
	 * @since 4.0
	 *
	 * @param array $displays metabox array
	 *
	 * @return array metabox array
	 */
	public function register_logger_displays( $displays ) {

		//Don't attempt to display logs if brute force isn't enabled
		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {

			$displays[] = array(
				'module'   => 'user_logging',
				'title'    => __( 'User Actions', 'it-l10n-ithemes-security-pro' ),
				'callback' => array( $this, 'logs_metabox_content' )
			);

		}

		return $displays;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @param  Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		//process strong passwords settings
		$input['enabled'] = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );

		if ( isset( $input['roll'] ) && ctype_alpha( wp_strip_all_tags( $input['roll'] ) ) ) {
			$input['roll'] = wp_strip_all_tags( $input['roll'] );
		}

		if ( is_multisite() ) {

			$this->core->show_network_admin_notice( false );

			$this->settings = $input;

		}

		return $input;

	}

	/**
	 * Prepare and save options in network settings
	 *
	 * @return void
	 */
	public function save_network_options() {

		if ( isset( $_POST['itsec_user_logging'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_pro-options' ) ) {
				die( __( 'Security error!', 'it-l10n-ithemes-security-pro' ) );
			}

			update_site_option( 'itsec_user_logging', $_POST['itsec_user_logging'] ); //we must manually save network options

		}

	}

	/**
	 * echos Enable Strong Passwords Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function user_logging_enabled() {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		$content = '<input type="checkbox" id="itsec_user_logging_enabled" name="itsec_user_logging[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		$content .= '<label for="itsec_user_logging_enabled"> ' . __( 'Enable user action logging.', 'it-l10n-ithemes-security-pro' ) . '</label>';

		echo $content;

	}

	/**
	 * echos Strong Passwords Role Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function user_logging_role() {

		if ( isset( $this->settings['roll'] ) ) {
			$roll = $this->settings['roll'];
		} else {
			$roll = 'administrator';
		}

		$content = '<select name="itsec_user_logging[roll]" id="itsec_user_logging_roll">';
		$content .= '<option value="administrator" ' . selected( $roll, 'administrator', false ) . '>' . translate_user_role( 'Administrator' ) . '</option>';
		$content .= '<option value="editor" ' . selected( $roll, 'editor', false ) . '>' . translate_user_role( 'Editor' ) . '</option>';
		$content .= '<option value="author" ' . selected( $roll, 'author', false ) . '>' . translate_user_role( 'Author' ) . '</option>';
		$content .= '<option value="contributor" ' . selected( $roll, 'contributor', false ) . '>' . translate_user_role( 'Contributor' ) . '</option>';
		$content .= '<option value="subscriber" ' . selected( $roll, 'subscriber', false ) . '>' . translate_user_role( 'Subscriber' ) . '</option>';
		$content .= '</select><br>';
		$content .= '<label for="itsec_user_logging_roll"> ' . __( 'Minimum role at which user actions are logged.' ) . '</label>';

		$content .= '<p class="description"> ' . __( 'For more information on WordPress roles and capabilities please see', 'it-l10n-ithemes-security-pro' ) . ' <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">http://codex.wordpress.org/Roles_and_Capabilities</a>.</p>';
		$content .= '<p class="warningtext description">' . __( 'Warning: If your site invites public registrations setting the role too low may result in some very large logs.', 'it-l10n-ithemes-security-pro' ) . '</p>';

		echo $content;

	}

	/**
	 * Adds fields that will be tracked for Google Analytics
	 *
	 * @since 4.0
	 *
	 * @param array $vars tracking vars
	 *
	 * @return array tracking vars
	 */
	public function tracking_vars( $vars ) {

		$vars['itsec_user_logging'] = array(
			'enabled' => '0:b',
			'roll'    => 'administrator:s',
		);

		return $vars;

	}

}