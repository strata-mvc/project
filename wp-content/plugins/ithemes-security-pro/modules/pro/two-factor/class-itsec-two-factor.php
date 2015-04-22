<?php

/**
 * Two factor Execution
 *
 * Handles all two factor execution once the feature has been
 * enabled by the user.
 *
 * @since   1.2.0
 *
 * @package iThemes_Security
 */
class ITSEC_Two_Factor {

	/**
	 * The module's saved options
	 *
	 * @since  1.2.0
	 * @access private
	 * @var array
	 */
	private $settings;

	/**
	 * The absolute web patch to the module's files
	 *
	 * @since  1.2.0
	 * @access private
	 * @var string
	 */
	private $module_path;

	/**
	 * Setup the module's functionality.
	 *
	 * Loads the two-factor module's unpriviledged functionality.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	function run() {

		$this->settings    = get_site_option( 'itsec_two_factor' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); //enqueue scripts for admin page
		add_action( 'edit_user_profile', array( $this, 'edit_user_profile' ) );
		add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ) );
		add_action( 'login_form', array( $this, 'login_form' ) );
		add_action( 'personal_options_update', array( $this, 'personal_options_update' ) );
		add_action( 'profile_personal_options', array( $this, 'profile_personal_options' ) );
		add_action( 'wp_ajax_itsec_two_factor_profile_ajax', array( $this, 'wp_ajax_itsec_two_factor_profile_ajax' ) );
		add_action( 'wp_ajax_itsec_two_factor_profile_new_app_pass_ajax', array( $this, 'wp_ajax_itsec_two_factor_profile_new_app_pass_ajax' ) );

		add_filter( 'itsec_logger_modules', array( $this, 'itsec_logger_modules' ) );
		add_filter( 'itsec_sync_modules', array( $this, 'itsec_sync_modules' ) ); //register sync modules
		add_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 10, 2 );

	}

	/**
	 * Add Files Admin Javascript
	 *
	 * Enqueues files used in the admin area for the file change module
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && 'profile' === get_current_screen()->id ) {

			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_register_style( 'itsec_two_factor_profile', $this->module_path . 'css/profile-two-factor.css', array(), $itsec_globals['plugin_build'] ); //add multi-select css
			wp_register_style( 'itsec_two_factor_profile', $this->module_path . 'css/profile-two-factor.css', array(), $itsec_globals['plugin_build'] ); //add multi-select css
			wp_enqueue_style( 'itsec_two_factor_profile' );
			wp_enqueue_script( 'jquery-qrcode', $this->module_path . 'js/jquery.qrcode.min.js', array( 'jquery' ), $itsec_globals['plugin_build'] );
			wp_enqueue_script( 'itsec_two_factor_profile', $this->module_path . 'js/profile-two-factor.js', array( 'jquery', 'backbone' ), $itsec_globals['plugin_build'], true );
			wp_localize_script(
				'itsec_two_factor_profile',
				'itsec_two_factor_profile',
				array(
					'nonce'        => wp_create_nonce( 'itsec_two_factor_profile' ),
					'passwords'    => $this->get_current_app_passwords(),
					'bad_name'     => __( 'You must enter a name for the password you are trying to enter', 'it-l10n-ithemes-security-pro' ),
					'dialog_title' => __( 'Application Password for', 'it-l10n-ithemes-security-pro' ),
					'dialog_text1' => __( 'You must right down the password for', 'it-l10n-ithemes-security-pro' ),
					'dialog_text2' => __( 'now.', 'it-l10n-ithemes-security-pro' ),
					'dialog_text3' => __( 'It will not be shown again.', 'it-l10n-ithemes-security-pro' ),
				)
			);

		}

	}

	/**
	 * Display user options field to allow override.
	 *
	 * Shows a field to an administrator that allows that administrator to override
	 * two factor for a given user by disabling it for that user.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $user user
	 *
	 * @return void
	 */
	public function edit_user_profile( $user ) {

		global $itsec_globals;

		if ( ( is_multisite() && true === current_user_can( 'manage_network_options' ) ) || ( false === is_multisite() && current_user_can( $itsec_globals['plugin_access_lvl'] ) ) ) {

			$enabled = trim( get_user_option( 'itsec_two_factor_enabled', $user->ID ) );

			echo '<h3>' . __( 'Google Authenticator Settings', 'it-l10n-ithemes-security-pro' ) . '</h3>';

			echo '<table class="form-table">';
			echo '<tbody>';

			echo '<tr>';
			echo '<th scope="row">' . __( 'Enable', 'it-l10n-ithemes-security-pro' ) . '</th>';
			echo '<td>';

			if ( 'on' === $enabled ) {

				echo '<input type="checkbox" name="itsec_two_factor_enabled" id="itsec_two_factor_enabled" ' . checked( $enabled, 'on', false ) . '/>';

			} else {

				echo __( 'Two-factor authentication has not been enabled for this user. The user can login and enable two-factor authentication themselves by editing their profile.', 'it-l10n-ithemes-security-pro' );

			}

			echo '</td>';
			echo '</tr>';

			echo '</tbody>';
			echo '</table>';

		}

	}

	/**
	 * Sanitize and update user option for override.
	 *
	 * Sanitizes and updates the two factor options when an administrator saves them for
	 * another user.
	 *
	 * @since 1.2.0
	 *
	 * @param int $user_id user id
	 *
	 * @return void
	 */
	public function edit_user_profile_update( $user_id ) {

		$current = trim( get_user_option( 'itsec_two_factor_enabled', $user_id ) );

		if ( 'on' === $current ) {

			if ( isset( $_POST['itsec_two_factor_enabled'] ) ) {

				$enabled = isset( $_POST['itsec_two_factor_enabled'] ) ? sanitize_text_field( $_POST['itsec_two_factor_enabled'] ) : 'off';

			} else {

				$enabled = 'off';
			}

			update_user_option( $user_id, 'itsec_two_factor_enabled', $enabled, true );

		}

	}

	/**
	 * Generates an app password.
	 *
	 * Generates an app password using 4 groups of 4 random characters.
	 *
	 * @since  1.2.0
	 *
	 * @access private
	 *
	 * @return string
	 */
	private function get_app_pass() {

		$pass = '';

		for ( $i = 0; 6 > $i; $i ++ ) {

			$pass .= ITSEC_Lib::get_random( 4 ) . ' ';

		}

		return strtoupper( trim( $pass ) );

	}

	/**
	 * Generate hash to check.
	 *
	 * Generates a two-factor hash based on key and time which can then be compared to the value entered.
	 *
	 * @since  1.2.0
	 *
	 * @access private
	 *
	 * @param string $key  the key to encode
	 * @param mixed  $time timestamp
	 *
	 * @return string the hash
	 */
	private function get_code( $key, $time = false ) {

		require_once( dirname( __FILE__ ) . '/lib/base32.php' );

		$base = new Base32();

		$secret = $base->toString( $key );

		if ( false === $time ) {
			$time = floor( time() / 30 );
		}

		$timestamp = pack( 'N*', 0 ) . pack( 'N*', $time );

		$hash = hash_hmac( 'sha1', $timestamp, $secret, true );

		$offset = ord( $hash[19] ) & 0xf;

		$code = (
			        ( ( ord( $hash[ $offset + 0 ] ) & 0x7f ) << 24 ) |
			        ( ( ord( $hash[ $offset + 1 ] ) & 0xff ) << 16 ) |
			        ( ( ord( $hash[ $offset + 2 ] ) & 0xff ) << 8 ) |
			        ( ord( $hash[ $offset + 3 ] ) & 0xff )
		        ) % pow( 10, 6 );

		return str_pad( $code, 6, '0', STR_PAD_LEFT );

	}

	/**
	 * Builds the list of app passwords.
	 *
	 * Builds a list of app passwords for return to the app password field.
	 *
	 * @since  1.14.0
	 *
	 * @access private
	 *
	 * @return string json string of result
	 */
	private function get_current_app_passwords() {

		$user = get_current_user_id();

		if ( $user === 0 ) {
			return json_encode( array( 'success' => false ) );
		}

		$saved_passwords = get_user_option( 'itsec_two_factor_app_pass', $user );

		if ( false === $saved_passwords ) {

			$app_passwords = array();

		} elseif ( ! is_array( $saved_passwords ) ) {

			$app_passwords = array(
				array(
					'id'   => 0,
					'name' => __( 'unknown', 'it-l10n-ithemes-security-pro' ),
					'pass' => $saved_passwords,
				)
			);

		}

		if ( false !== $saved_passwords ) {

			$count = 0;

			foreach ( $saved_passwords as $key => $value ) {

				$app_passwords[ $count ] = array(
					'id'   => $key,
					'name' => $key,
					'pass' => '---- ---- ---- ----',
				);

				$count ++;

			}

		}

		return json_encode( $app_passwords );

	}

	/**
	 * Register two factor for logger.
	 *
	 * Registers the two factor module with the core logger functionality.
	 *
	 * @since 1.2.0
	 *
	 * @param  array $logger_modules array of logger modules
	 *
	 * @return array array of logger modules
	 */
	public function itsec_logger_modules( $logger_modules ) {

		$logger_modules['two_factor'] = array(
			'type'     => 'two_factor',
			'function' => __( 'Two Factor Login Failure', 'it-l10n-ithemes-security-pro' ),
		);

		return $logger_modules;

	}

	/**
	 * Register two factor for Sync
	 *
	 * Reigsters iThemes Sync verbs for the two factor module.
	 *
	 * @since 1.12.0
	 *
	 * @param  array $sync_modules array of sync modules
	 *
	 * @return array array of sync modules
	 */
	public function itsec_sync_modules( $sync_modules ) {

		$sync_modules['two_factor'] = array(
			'verbs'      => array(
				'itsec-get-two-factor-users'     => 'Ithemes_Sync_Verb_ITSEC_Get_Two_Factor_Users',
				'itsec-override-two-factor-user' => 'Ithemes_Sync_Verb_ITSEC_Override_Two_Factor_User',
			),
			'everything' => 'itsec-get-two-factor-users',
			'path'       => dirname( __FILE__ ),
		);

		return $sync_modules;

	}

	/**
	 * Add authenticator field to login form.
	 *
	 * Adds the field asking for the two-factor login token to the login form.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	function login_form() {

		echo '<p>';
		echo '<label for="itsec_two_factor_code">' . __( 'Two-factor Authentication Code', 'it-l10n-ithemes-security-pro' ) . '<br />';
		echo '<input type="text" name="itsec_two_factor_code" id="itsec_two_factor_code" class="input" value="" size="20" style="ime-mode: inactive;" /></label>';
		echo '</p>';
	}

	/**
	 * Sanitize and update user options.
	 *
	 * Sanitizes and updates user options when a user saves two-factor settings
	 * on their own profile.
	 *
	 * @since 1.2.0
	 *
	 * @param int $user_id user id
	 *
	 * @return void
	 */
	public function personal_options_update( $user_id ) {

		$enabled       = 'off';
		$enabled_input = isset( $_POST['itsec_two_factor_enabled'] ) ? sanitize_text_field( $_POST['itsec_two_factor_enabled'] ) : 'off';
		$description   = isset( $_POST['itsec_two_factor_description'] ) ? sanitize_text_field( $_POST['itsec_two_factor_description'] ) : ITSEC_Lib::get_domain( get_site_url(), false, false );
		$key           = isset( $_POST['itsec_two_factor_key'] ) ? sanitize_text_field( $_POST['itsec_two_factor_key'] ) : ITSEC_Lib::get_random( 16, true );
		$use_app       = isset( $_POST['itsec_two_factor_use_app'] ) ? sanitize_text_field( $_POST['itsec_two_factor_use_app'] ) : 'off';

		$app_passes = array();

		if ( isset( $_POST['itsec_app_pass'] ) ) {

			$saved_passes = get_user_option( 'itsec_two_factor_app_pass', $user_id );

			if ( false === $saved_passes ) {

				$saved_passes = array();

			} elseif ( ! is_array( $saved_passes ) ) {

				$app_passes = array(
					__( 'unknown', 'it-l10n-ithemes-security-pro' ) => $saved_passes,
				);

			}

			//Prevent duplicates or changing password to all hashes
			foreach ( $_POST['itsec_app_pass'] as $app_pass ) {

				$name = sanitize_text_field( trim( $app_pass['name'] ) );

				if ( ! isset( $saved_passes[ $name ] ) ) {

					$pass = wp_hash_password( strtoupper( str_replace( ' ', '', sanitize_text_field( trim( $app_pass['pass'] ) ) ) ) );

					$app_passes[ $name ] = $pass;

				} else {

					$app_passes[ $name ] = $saved_passes[ $name ];

				}

			}

		}

		$time = floor( time() / 30 ); //time to check

		if ( ( 'off' === get_user_option( 'itsec_two_factor_enabled', $user_id ) && 'on' === $enabled_input ) || ( $key !== get_user_option( 'itsec_two_factor_key', $user_id ) ) ) {

			if ( isset( $_POST['itsec_two_factor_confirm'] ) ) {

				$code = sanitize_text_field( trim( $_POST['itsec_two_factor_confirm'] ) );

			} else {

				$code = false;

			}

			if ( false !== $code && 0 < strlen( $code ) ) {

				$good_code = false;

				$offset = isset( $this->settings['offset'] ) ? intval( $this->settings['offset'] ) : 1;

				//Check both sides of the time
				for ( $i = - $offset; $i <= $offset; $i ++ ) {

					$log_time = $time + $i;

					if ( $this->get_code( $key, $log_time ) === $code ) {

						$enabled   = $enabled_input;
						$good_code = true;

					}

				}

			} else {

				$good_code = false;

			}

			if ( false === $good_code ) {
				add_action( 'user_profile_update_errors', array( $this, 'user_profile_update_errors' ), 10, 3 );
			}

		} else {

			$enabled = $enabled_input;

		}

		update_user_option( $user_id, 'itsec_two_factor_enabled', $enabled, true );
		update_user_option( $user_id, 'itsec_two_factor_description', $description, true );
		update_user_option( $user_id, 'itsec_two_factor_key', $key, true );

		if ( 'off' !== $use_app ) {

			if ( 1 > sizeof( $app_passes ) ) {

				$use_app = 'off';
				delete_user_option( $user_id, 'itsec_two_factor_app_pass', true );

			} else {

				update_user_option( $user_id, 'itsec_two_factor_app_pass', $app_passes, true );

			}

		}

		update_user_option( $user_id, 'itsec_two_factor_use_app', $use_app, true );

	}

	/**
	 * Display user options fields.
	 *
	 * Displays two factor settings on a user's own profile options.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $user user
	 *
	 * @return void
	 */
	public function profile_personal_options( $user ) {

		//determine the minimum role for enforcement
		$min_role = isset( $this->settings['roll'] ) ? $this->settings['roll'] : $this->settings['roll'];

		//all the standard roles and level equivalents
		$available_roles = array(
			'administrator' => '8', 'editor' => '5', 'author' => '2', 'contributor' => '1', 'subscriber' => '0'
		);

		$allowed_two_factor = false;

		foreach ( $user->roles as $capability ) {

			if ( isset( $available_roles[ $capability ] ) && $available_roles[ $capability ] >= $available_roles[ $min_role ] ) {
				$allowed_two_factor = true;
			}

		}

		if ( true === $allowed_two_factor ) {

			$enabled     = trim( get_user_option( 'itsec_two_factor_enabled', $user->ID ) );
			$domain      = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			$description = false !== get_user_option( 'itsec_two_factor_description', $user->ID ) ? trim( get_user_option( 'itsec_two_factor_description', $user->ID ) ) : $domain;
			$key         = false !== get_user_option( 'itsec_two_factor_key', $user->ID ) ? trim( get_user_option( 'itsec_two_factor_key', $user->ID ) ) : ITSEC_Lib::get_random( 16, true );
			$use_app     = trim( get_user_option( 'itsec_two_factor_use_app', $user->ID ) );
			$app_pass    = false !== get_user_option( 'itsec_two_factor_app_pass', $user->ID ) ? '---- ---- ---- ----' : $this->get_app_pass();

			echo '<h3>' . __( 'Google Authenticator Settings', 'it-l10n-ithemes-security-pro' ) . '</h3>';

			echo '<table class="form-table">';
			echo '<tbody>';

			echo '<tr>';
			echo '<th scope="row">' . __( 'Enable', 'it-l10n-ithemes-security-pro' ) . '</th>';
			echo '<td>';
			echo '<input type="checkbox" name="itsec_two_factor_enabled" id="itsec_two_factor_enabled" ' . checked( $enabled, 'on', false ) . '/>';
			echo '</td>';
			echo '</tr>';

			echo '</tbody>';
			echo '</table>';

			echo '<div id="itsec_two_factor_settings">';
			echo '<table class="form-table">';
			echo '<tbody>';

			echo '<tr>';
			echo '<th scope="row">' . __( 'Description', 'it-l10n-ithemes-security-pro' ) . '</th>';
			echo '<td>';
			echo '<label for="itsec_two_factor_description">';
			echo '<input type="text" name="itsec_two_factor_description" id="itsec_two_factor_description" value="' . $description . '"/> ';
			echo __( 'A label that will identify the site in your Google Authenticator app.', 'it-l10n-ithemes-security-pro' );
			echo '</label>';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th scope="row">' . __( 'Key', 'it-l10n-ithemes-security-pro' ) . '</th>';
			echo '<td>';
			echo '<input type="text" name="itsec_two_factor_key" id="itsec_two_factor_key" readonly="readonly" value="' . $key . '"/> ';
			echo '<input type="button" class="button" name="itsec_two_factor_get_new_key" id="itsec_two_factor_get_new_key" value="' . __( 'Get new key', 'it-l10n-ithemes-security-pro' ) . '" />';
			echo '</label>';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td></td>';
			echo '<td>';
			echo '<div id="qrcode"/></div>';
			echo '<p class="description">' . __( 'Scan this code with your Google Authenticator app.', 'it-l10n-ithemes-security-pro' ) . '</p>';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th scope="row">' . __( 'Confirm Code', 'it-l10n-ithemes-security-pro' ) . '</th>';
			echo '<td>';
			echo '<input type="text" name="itsec_two_factor_confirm" id="itsec_two_factor_confirm" value=""/> ';
			echo __( 'Confirm the current key from your two-factor application.', 'it-l10n-ithemes-security-pro' );
			echo '</label>';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th scope="row">' . __( 'Use App Password', 'it-l10n-ithemes-security-pro' ) . '</th>';
			echo '<td>';
			echo '<label for="itsec_two_factor_use_app">';
			echo '<input type="checkbox" name="itsec_two_factor_use_app" id="itsec_two_factor_use_app" ' . checked( $use_app, 'on', false ) . '/> ';
			echo __( 'Create a unique password to log into applications that do not support two-factor authentication. This will reduce the security of your user account.', 'it-l10n-ithemes-security-pro' );
			echo '</label>';
			echo '</td>';
			echo '</tr>';

			echo '</tbody>';
			echo '</table>';

			echo '<div id="itsec_two_factor_app_pass_settings">';
			echo '<table class="form-table">';
			echo '<tbody>';

			echo '<tr>';
			echo '<th></th>';
			echo '<td>';
			require( dirname( __FILE__ ) . '/templates/app_pass.tmpl.php' );
			echo '</td>';
			echo '</tr>';

			echo '</tbody>';
			echo '</table>';
			echo '</div>';

			echo '</div>';

		}

	}

	/**
	 * Display error message when GA not confirmed.
	 *
	 * Shows an error when two-factor is not correctly confirmed during activation of the feature
	 * on a given user profile. This is most likely due to time issues on the server.
	 *
	 * @since 1.3.0
	 *
	 * @param array   &$errors An array of user profile update errors, passed by reference.
	 * @param bool    $update  Whether this is a user update.
	 * @param WP_User &$user   WP_User object, passed by reference.
	 *
	 * @return void
	 */
	public function user_profile_update_errors( &$errors, $update, &$user ) {

		$errors->add( 'user_error', __( 'Your Two-factor confirmation code is incorrect and Two-factor authentication has been disabled. Please check your server time and try again.', 'it-l10n-ithemes-security-pro' ) );

	}

	/**
	 * Ajax generate new key.
	 *
	 * Generates a new two-factor key via AJAX.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function wp_ajax_itsec_two_factor_profile_ajax() {

		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_two_factor_profile' ) ) {
			die( __( 'Security error!', 'it-l10n-ithemes-security-pro' ) );
		}

		die( ITSEC_Lib::get_random( 16, true ) );

	}

	/**
	 * Ajax generate new app password.
	 *
	 * Uses AJAX to generate a new app password that can be sent directly to the form.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function wp_ajax_itsec_two_factor_profile_new_app_pass_ajax() {

		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_two_factor_profile' ) ) {
			die( __( 'Security error!', 'it-l10n-ithemes-security-pro' ) );
		}

		die( $this->get_app_pass() );

	}

	/**
	 * Authenticate a user with two-factor enabled.
	 *
	 * Checks for a valid two-factor token or app password upon user authentication.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed  $user     the user
	 * @param string $password password the password entered
	 *
	 * @return mixed user or error
	 */
	public function wp_authenticate_user( $user, $password ) {

		global $itsec_logger, $itsec_globals;

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$current_user = $user; //Store error or user object already authenticated

		$override         = intval( get_user_option( 'itsec_two_factor_override', $user->ID ) ) === 1 ? true : false;
		$override_expires = intval( get_user_option( 'itsec_two_factor_override_expires', $user->ID ) );

		if ( true === $override && $itsec_globals['current_time'] < $override_expires ) {
			return $user; //Override is active so just return the user.
		}

		if ( true === $override ) { //Delete the options if they've expired

			delete_user_option( $user->ID, 'itsec_two_factor_override', true );
			delete_user_option( $user->ID, 'itsec_two_factor_override_expires', true );

		}

		//make sure the user has two-factor turned on for their account
		if ( isset( $user->ID ) && 'on' === trim( get_user_option( 'itsec_two_factor_enabled', $user->ID ) ) ) {

			$key        = get_user_option( 'itsec_two_factor_key', $user->ID );
			$time       = floor( time() / 30 ); //time to check
			$good_login = false; //is this a valid login

			if ( isset( $_POST['itsec_two_factor_code'] ) ) {

				$code = sanitize_text_field( trim( $_POST['itsec_two_factor_code'] ) );

			} else {

				$code = false;

			}

			if ( false !== $code && 0 < strlen( $code ) ) {

				$offset = isset( $this->settings['offset'] ) ? intval( $this->settings['offset'] ) : 1;

				//Check both sides of the time
				for ( $i = - $offset; $i <= $offset; $i ++ ) {

					$log_time = $time + $i;

					if ( $this->get_code( $key, $log_time ) === $code ) {

						$good_login = array( $log_time, $code, ); //they gave a valid code

					}

				}

			}

			if ( false !== $good_login ) { //we have a valid code

				$last_login = get_user_option( 'itsec_two_factor_last_login', $user->ID );

				if ( is_array( $last_login ) && ( $last_login[1] === $good_login[1] || $last_login[0] >= $good_login[0] ) ) { //looks like a replay

					$itsec_logger->log_event(
						'two_factor',
						8,
						array(
							__( 'Possible two-factor relay attack. Two factor code was re-used or invalid time.', 'it-l10n-ithemes-security-pro' ),
						),
						ITSEC_Lib::get_ip(),
						sanitize_text_field( $user->user_login ),
						'',
						'',
						''
					);

					return new WP_Error( 'invalid_two_factor_code', '<strong>' . __( 'ERROR', 'it-l10n-ithemes-security-pro' ) . '</strong>: ' . __( 'The two-factor code entered is invalid. Please try again.', 'it-l10n-ithemes-security-pro' ) );

				} else { //its a good login so save the info

					update_user_option( $user->ID, 'itsec_two_factor_last_login', $good_login );

				}

			} elseif ( defined( 'XMLRPC_REQUEST' ) && 'on' === trim( get_user_option( 'itsec_two_factor_use_app', $user->ID ) ) ) { //code is invalid, lets check the app password if its on

				$good_login    = false;
				$app_passwords = get_user_option( 'itsec_two_factor_app_pass', $user->ID );

				if ( false !== $app_passwords && ! is_array( $app_passwords ) && wp_check_password( strtoupper( str_replace( ' ', '', sanitize_text_field( $password ) ) ), $app_passwords ) ) {
					$good_login = true;
				}

				if ( false !== $app_passwords && is_array( $app_passwords ) ) {

					foreach ( $app_passwords as $app_password ) {

						if ( wp_check_password( strtoupper( str_replace( ' ', '', sanitize_text_field( $password ) ) ), $app_password ) ) {
							$good_login = true;
						}

					}

				}

				if ( true === $good_login ) {

					$user->user_pass = wp_hash_password( $password );

					return $user;

				} else {

					return new WP_Error( 'invalid_two_factor_app_password', '<strong>' . __( 'ERROR', 'it-l10n-ithemes-security-pro' ) . '</strong>: ' . __( 'The two-factor app password entered is invalid. Please try again.', 'it-l10n-ithemes-security-pro' ) );

				}

			} else {

				return new WP_Error( 'invalid_two_factor_code', '<strong>' . __( 'ERROR', 'it-l10n-ithemes-security-pro' ) . '</strong>: ' . __( 'The two-factor code entered is invalid. Please try again.', 'it-l10n-ithemes-security-pro' ) );

			}

		}

		return $current_user;

	}

}