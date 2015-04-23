<?php

class ITSEC_Password {

	private
		$settings,
		$module_path;

	function run() {

		$this->settings    = get_site_option( 'itsec_password' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'user_profile_update_errors', array( $this, 'validate_valid_password' ), 11 ); //make sure to clear password nag
		add_action( 'validate_password_reset', array( $this, 'validate_valid_password' ), 11 ); //make sure to clear password nag if reseting
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); //Add password generator to edit profile page
		add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) ); //Add to reset password page
		add_action( 'wp_login', array( $this, 'wp_login' ), 10, 2 ); //set meta if they need to change their password
		add_action( 'current_screen', array( $this, 'admin_init' ) ); //redirect to profile page and show a require password change nag

	}

	/**
	 * Add two-factor Javascript
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		if ( isset( get_current_screen()->id ) && ( get_current_screen()->id === 'profile' || get_current_screen()->id === 'user' ) ) { //this should only run on profile pages

			$this->generate_script();

		}

	}

	/**
	 * Process redirection of all dashboard pages for password reset
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function admin_init() {

		if ( isset( get_current_screen()->id ) && get_current_screen()->id !== 'profile' ) {

			if ( isset( $this->settings['expire'] ) && $this->settings['expire'] === true ) { //make sure we're enforcing a password change

				$current_user = wp_get_current_user();

				if ( isset( $current_user->ID ) && $current_user->ID !== 0 ) { //make sure we have a valid user

					$required = get_user_meta( $current_user->ID, 'itsec_password_change_required', true );

					if ( $required == true ) {

						wp_safe_redirect( admin_url( 'profile.php?itsec_password_expired=true#pass1' ) );
						exit();

					}

				}

			}

		}

	}

	private function generate_script() {

		global $itsec_globals;

		$user = wp_get_current_user();

		if ( isset( $_GET['action'] ) && $_GET['action'] == 'rp' ) {

			$allowed_generator = true;

		} else {

			//determine the minimum role for enforcement
			$min_role = isset( $this->settings['generate_role'] ) ? $this->settings['generate_role'] : 'administrator';

			//all the standard roles and level equivalents
			$available_roles = array(
				'administrator' => '8',
				'editor'        => '5',
				'author'        => '2',
				'contributor'   => '1',
				'subscriber'    => '0'
			);

			$allowed_generator = false;

			foreach ( $user->roles as $capability ) {

				if ( isset( $available_roles[ $capability ] ) && $available_roles[ $capability ] >= $available_roles[ $min_role ] ) {
					$allowed_generator = true;
				}

			}

		}

		if ( $allowed_generator === true ) {

			$password_expired = false;

			if ( isset( $_GET['itsec_password_expired'] ) && $_GET['itsec_password_expired'] == 'true' ) {
				$password_expired = true;
			}

			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_script( 'itsec_password_generator', $this->module_path . 'js/password.js', array( 'jquery' ), $itsec_globals['plugin_build'] );
			wp_localize_script( 'itsec_password_generator', 'itsec_password_generator', array(
				'generator'        => isset( $this->settings['generate'] ) && $this->settings['generate'] === true ? 1 : 0,
				'text1'            => __( 'Generate Strong Password', 'it-l10n-ithemes-security-pro' ),
				'text3'            => __( 'Your New Password', 'it-l10n-ithemes-security-pro' ),
				'text2'            => __( 'Your new password is listed below. You must use this password the next time you log in to this site, so copy it to a secure location.', 'it-l10n-ithemes-security-pro' ),
				'base_length'      => isset( $this->settings['generate_length'] ) ? absint( $this->settings['generate_length'] ) : 50,
				'password_expired' => $password_expired,
				'text4'            => $itsec_globals['plugin_name'] . __( ' has noticed that your password has expired and must be reset. Please choose a new password to continue.', 'it-l10n-ithemes-security-pro' ),
			) );

		}

	}

	public function login_enqueue_scripts() {

		$this->generate_script();

	}

	/**
	 * Check for errors in password submission and update meta accordingly
	 *
	 * This will run whether password expiration is used directly or not to make it easier for users to handle later
	 *
	 * @since 1.8
	 *
	 * @param object $errors WordPress errors
	 *
	 * @return object WordPress error object
	 *
	 **/
	public function validate_valid_password( $errors ) {

		global $itsec_globals;

		if ( isset( $this->settings['expire'] ) && $this->settings['expire'] === true ) {

			$user = wp_get_current_user();

			if ( $user instanceof WP_User ) {

				if ( wp_check_password( isset( $_POST['pass1'] ) ? $_POST['pass1'] : '', isset( $user->data->user_pass ) ? $user->data->user_pass : false, $user->ID ) ) {
					$errors->add( 'pass', __( '<strong>ERROR</strong>: The password you have chosen appears to have been used before. You must choose a new password.', 'it-l10n-ithemes-security-pro' ) );
				}

				if ( is_wp_error( $errors ) && empty( $errors->errors ) && isset( $_POST['pass1'] ) && strlen( trim( $_POST['pass1'] ) ) > 0 ) {

					$current_user = get_current_user_id();

					delete_user_meta( $current_user, 'itsec_password_change_required' );
					update_user_meta( $current_user, 'itsec_last_password_change', $itsec_globals['current_time_gmt'] );

				}

			}

		}

		return $errors;

	}

	/**
	 * Handle redirection to password change form on login
	 *
	 * @since 1.8
	 *
	 * @param string $username the username attempted
	 * @param        object    wp_user the user
	 *
	 * @return bool|void false on failure
	 */
	public function wp_login( $username, $user = null ) {

		global $itsec_globals;

		if ( isset( $this->settings['expire'] ) && $this->settings['expire'] === true ) {

			//Get a valid user or terminate the hook (all we care about is forcing the password change... Let brute force protection handle the rest
			if ( $user !== null ) {

				$current_user = $user;

			} elseif ( is_user_logged_in() ) {

				$current_user = wp_get_current_user();

			} else {

				return false;

			}

			//determine the minimum role for enforcement
			$min_role = isset( $this->settings['expire_role'] ) ? $this->settings['expire_role'] : 'administrator';

			//all the standard roles and level equivalents
			$available_roles = array(
				'administrator' => '8',
				'editor'        => '5',
				'author'        => '2',
				'contributor'   => '1',
				'subscriber'    => '0'
			);

			$allowed_expire = false;

			foreach ( $current_user->roles as $capability ) {

				if ( isset( $available_roles[ $capability ] ) && $available_roles[ $capability ] >= $available_roles[ $min_role ] ) {
					$allowed_expire = true;
				}

			}

			if ( $allowed_expire === true ) {

				$last_change = get_user_meta( $current_user->ID, 'itsec_last_password_change', true );

				if ( isset( $this->settings['expire_force'] ) && $this->settings['expire_force'] !== false ) {

					$oldest_allowed = $this->settings['expire_force'];

				} else {

					$oldest_allowed = $itsec_globals['current_time_gmt'] - ( isset( $this->settings['expire_max'] ) ? absint( $this->settings['expire_max'] ) * 86400 : 10368000 );

				}

				if (
					$last_change === false || //They've never changed their password (at least not since the feature was added)
					$last_change <= $oldest_allowed //they haven't changed their password since before the admin required a forced reset
				) {

					update_user_meta( $current_user->ID, 'itsec_password_change_required', true );

				}

			}

		}

	}

}