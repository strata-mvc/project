<?php

class ITSEC_Recaptcha {

	private
		$settings;

	function run() {

		$this->settings = get_site_option( 'itsec_recaptcha' );

		add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );

		add_filter( 'itsec_lockout_modules', array( $this, 'itsec_lockout_modules' ) );
		add_filter( 'itsec_logger_modules', array( $this, 'itsec_logger_modules' ) );

		if ( isset( $this->settings['comments'] ) && true === $this->settings['comments'] ) {

			add_filter( 'comment_form_defaults', array( $this, 'comment_form_defaults' ) );
			add_filter( 'preprocess_comment', array( $this, 'preprocess_comment' ) );

		}

		if ( isset( $this->settings['login'] ) && true === $this->settings['login'] ) {

			add_action( 'login_form', array( $this, 'login_form' ) );
			add_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ) );

		}

		if ( isset( $this->settings['register'] ) && true === $this->settings['register'] ) {

			add_action( 'register_form', array( $this, 'register_form' ) );
			add_filter( 'registration_errors', array( $this, 'registration_errors' ) );

		}

	}

	/**
	 * Add recaptcha form to comment form
	 *
	 * @since 1.13
	 *
	 * @param array $defaults The default comment form arguments.
	 *
	 * @return array  The default comment form arguments.
	 */
	public function comment_form_defaults( $defaults ) {

		$defaults['comment_notes_after'] = $this->show_field( false );

		return $defaults;

	}

	/**
	 * Register recaptcha for lockout
	 *
	 * @since 1.13
	 *
	 * @param  array $lockout_modules array of lockout modules
	 *
	 * @return array                   array of lockout modules
	 */
	public function itsec_lockout_modules( $lockout_modules ) {

		if ( $this->settings['enabled'] === true ) {

			$lockout_modules['recaptcha'] = array(
				'type'   => 'recaptcha',
				'reason' => __( 'too many failed captcha submissions.', 'it-l10n-ithemes-security-pro' ),
				'host'   => isset( $this->settings['error_threshold'] ) ? absint( $this->settings['error_threshold'] ) : 7,
				'period' => isset( $this->settings['check_period'] ) ? absint( $this->settings['check_period'] ) : 5,
			);

		}

		return $lockout_modules;

	}

	/**
	 * Register recaptcha detection for logger
	 *
	 * @since 1.13
	 *
	 * @param  array $logger_modules array of logger modules
	 *
	 * @return array                   array of logger modules
	 */
	public function itsec_logger_modules( $logger_modules ) {

		if ( $this->settings['enabled'] === true ) {

			$logger_modules['recaptcha'] = array(
				'type'     => 'recaptcha',
				'function' => __( 'Failed Recaptcha submission', 'it-l10n-ithemes-security-pro' ),
			);

		}

		return $logger_modules;

	}

	/**
	 * Add appropriate scripts to login page
	 *
	 * @since 1.13
	 *
	 * @return void
	 */
	public function login_enqueue_scripts() {

		global $itsec_globals;

		$module_path = ITSEC_Lib::get_module_path( __FILE__ );

		wp_register_style( 'itsec-recaptcha', $module_path . 'css/itsec-recaptcha.css', array(), $itsec_globals['plugin_build'] );
		wp_enqueue_style( 'itsec-recaptcha' );

	}

	/**
	 * Add the recaptcha field to the login form
	 *
	 * @since 1.13
	 *
	 * @return void
	 */
	public function login_form() {

		$this->show_field( true, true, 10, 0, 10 );

	}

	/**
	 * Process recaptcha for comments
	 *
	 * @since 1.13
	 *
	 * @param array $comment_data Comment data.
	 *
	 * @return array Comment data.
	 */
	function preprocess_comment( $comment_data ) {

		$success = $this->validate_captcha();

		if ( 1 === $success ) {
			return $comment_data;
		}

		switch ( $success ) {

			case - 1:
				wp_die( __( 'You must verify you are indeed a human to post a comment on this site', 'it-l10n-ithemes-security-pro' ) );
				break;
			case 0:
				wp_die( __( 'The captcha response you submitted does not appear to be valid. Please try again.', 'it-l10n-ithemes-security-pro' ) );
				break;
			default:
				wp_die( __( 'We cannot verify that you are indeed human. Please try again.', 'it-l10n-ithemes-security-pro' ) );
				break;

		}

		return $comment_data;

	}

	/**
	 * Add the recaptcha field to the registration form
	 *
	 * @since 1.13
	 *
	 * @return void
	 */
	public function register_form() {

		$this->show_field( true, true, 10, 0, 10 );

	}

	/**
	 * Set the registration error if captcha wasn't validated
	 *
	 * @since 1.13
	 *
	 * @param WP_Error $errors               A WP_Error object containing any errors encountered
	 *                                       during registration.
	 *
	 * @return WP_Error A WP_Error object containing any errors encountered
	 *                                       during registration.
	 */
	public function registration_errors( $errors ) {

		$success = $this->validate_captcha();

		switch ( $success ) {

			case - 1:
				$errors->add( 'recaptcha_error', __( 'You must verify you are indeed a human to register for this site', 'it-l10n-ithemes-security-pro' ) );
				break;
			case 0:
				$errors->add( 'recaptcha_error', __( 'The captcha response you submitted does not appear to be valid. Please try again.', 'it-l10n-ithemes-security-pro' ) );
				break;
			case - 2:
				$errors->add( 'recaptcha_error', __( 'We cannot verify that you are indeed human. Please try again.', 'it-l10n-ithemes-security-pro' ) );
				break;

		}

		return $errors;

	}

	/**
	 * Shows the recaptcha field
	 *
	 * @since 1.13
	 *
	 * @param bool $echo          true to echo or return
	 * @param bool $noscript      true to ech or return noscript information
	 * @param int  $margin_top    the margin above the box
	 * @param int  $margin_right  the margin to the right of the box
	 * @param int  $margin_bottom the margin below the box
	 * @param int  $margin_left   the margin to the left of the box
	 * @param bool $ajax_load     [Deprecated] unused variable
	 *
	 * @return String the field string
	 */
	public static function show_field( $echo = true, $noscript = true, $margin_top = 0, $margin_right = 0, $margin_bottom = 0, $margin_left = 0, $ajax_load = null ) {

		$field         = '';
		$margin_top    = absint( $margin_top );
		$margin_left   = absint( $margin_left );
		$margin_right  = absint( $margin_right );
		$margin_bottom = absint( $margin_bottom );
		$settings      = get_site_option( 'itsec_recaptcha' );
		$language      = isset( $settings['language'] ) ? '?hl=' . esc_attr( $settings['language'] ) : '';
		$theme         = isset( $settings['theme'] ) && true === $settings['theme'] ? 'dark' : 'light';

		if ( ! isset( $settings['site_key'] ) ) {
			return false;
		}

		$field .= '<script src="https://www.google.com/recaptcha/api.js' . $language . '" async defer></script>';
		$field .= '<div data-theme="' . $theme . '" style="margin: ' . $margin_top . 'px ' . $margin_right . 'px ' . $margin_bottom . 'px ' . $margin_left . 'px;" class="g-recaptcha" data-sitekey="' . $settings['site_key'] . '"></div>';

		if ( true === $noscript ) {
			$field .= '<noscript>
                <div style="width: 302px; height: 352px;">
                    <div style="width: 302px; height: 352px; position: relative;">
                        <div style="width: 302px; height: 352px; position: absolute;">
                            <iframe src="https://www.google.com/recaptcha/api/fallback?k=' . $settings['site_key'] . '" frameborder="0" scrolling="no" style="width: 302px; height:352px; border-style: none;"></iframe>
                        </div>
                        <div style="width: 250px; height: 80px; position: absolute; border-style: none; bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
                            <textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 80px; border: 1px solid #c1c1c1; margin: 0px; padding: 0px; resize: none;" value=""></textarea>
                        </div>
                    </div>
                </div>
			</noscript>';
		}

		if ( true === $echo ) {
			echo $field;
		}

		return $field;

	}

	/**
	 * Validates the captcha code
	 *
	 * @since 1.13
	 *
	 * @return int status of captcha
	 */
	public static function validate_captcha() {

		global $itsec_lockout, $itsec_logger;

		$settings = get_site_option( 'itsec_recaptcha' );

		if ( ! isset( $settings['site_key'] ) ) {
			return - 2;
		}

		if ( ! isset( $_POST['g-recaptcha-response'] ) || empty( $_POST['g-recaptcha-response'] ) ) {

			$itsec_logger->log_event(
				'recaptcha',
				5,
				array(),
				ITSEC_Lib::get_ip(),
				'',
				'',
				esc_sql( $_SERVER['REQUEST_URI'] ),
				isset( $_SERVER['HTTP_REFERER'] ) ? esc_sql( $_SERVER['HTTP_REFERER'] ) : ''
			);

			$itsec_lockout->do_lockout( 'recaptcha' );

			return - 1; //captcha form not submitted

		} else {

			$url = add_query_arg(
				array(
					'secret'   => $settings['secret_key'],
					'response' => esc_attr( $_POST['g-recaptcha-response'] ),
					'remoteip' => ITSEC_Lib::get_ip(),
				),
				'https://www.google.com/recaptcha/api/siteverify'
			);

			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {

				$status = json_decode( $response['body'] );

				if ( isset( $status->success ) ) {

					return 1; //captcha validated successfully

				} else {

					$itsec_logger->log_event(
						'recaptcha',
						5,
						array(),
						ITSEC_Lib::get_ip(),
						'',
						'',
						esc_sql( $_SERVER['REQUEST_URI'] ),
						isset( $_SERVER['HTTP_REFERER'] ) ? esc_sql( $_SERVER['HTTP_REFERER'] ) : ''
					);

					$itsec_lockout->do_lockout( 'recaptcha' );

					return 0; //incorrect captcha entered

				}

			} else {

				return - 2; //captcha couldn't be validated

			}

		}

	}

	/**
	 * Set the login error if captcha wasn't validated
	 *
	 * @since 1.13
	 *
	 * @param WP_User|WP_Error $user     WP_User or WP_Error object if a previous
	 *                                   callback failed authentication.
	 *
	 * @return WP_User|WP_Error     WP_User or WP_Error object if a previous
	 *                                   callback failed authentication.
	 */
	public function wp_authenticate_user( $user ) {

		if ( is_wp_error( $user ) || defined( 'XMLRPC_REQUEST' ) ) { //don't need to stop xmlrpc requests or process if we already have an error
			return $user;
		}

		$success = $this->validate_captcha();

		switch ( $success ) {

			case - 1:
				return new WP_Error( 'recaptcha_error', __( 'You must verify you are indeed a human to login to this site', 'it-l10n-ithemes-security-pro' ) );
				break;
			case 0:
				return new WP_Error( 'recaptcha_error', __( 'The captcha response you submitted does not appear to be valid. Please try again.', 'it-l10n-ithemes-security-pro' ) );
				break;
			case - 2:
				return new WP_Error( 'recaptcha_error', __( 'We cannot verify that you are indeed human. Please try again.', 'it-l10n-ithemes-security-pro' ) );
				break;

		}

		return $user;

	}

}