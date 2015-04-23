<?php

class ITSEC_Recaptcha_Admin {

	private
		$settings,
		$core,
		$module_path;

	function run( $core ) {

		$this->core        = $core;
		$this->settings    = get_site_option( 'itsec_recaptcha' );
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
	 * Add Recaptcha admin Javascript
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_pro' ) !== false ) {

			wp_enqueue_script( 'itsec_recaptcha_js', $this->module_path . 'js/admin-recaptcha.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function itsec_add_admin_meta_boxes() {

		$id    = 'recaptcha_options';
		$title = __( 'reCAPTCHA', 'it-l10n-ithemes-security-pro' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_recaptcha_settings' ),
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
	 * @since 1.14
	 *
	 * @param array $statuses array of statuses
	 *
	 * @return array array of statuses
	 */
	public function itsec_add_dashboard_status( $statuses ) {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true && ( ( isset( $this->settings['comments'] ) && $this->settings['comments'] === true ) || ( isset( $this->settings['login'] ) && $this->settings['login'] === true ) ) ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'You are blocking bots wih Recaptcha', 'it-l10n-ithemes-security-pro' ), 'link' => '#itsec_recaptcha_enabled', 'pro' => true, );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'You are not blocking bots with Recaptcha.', 'it-l10n-ithemes-security-pro' ), 'link' => '#itsec_recaptcha_enabled', 'pro' => true, );

		}

		array_push( $statuses[ $status_array ], $status );

		return $statuses;

	}

	/**
	 * Execute admin initializations
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function itsec_admin_init() {

		//Add Settings sections
		add_settings_section(
			'recaptcha-enabled',
			__( 'Enable reCAPTCHA', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		add_settings_section(
			'recaptcha-settings',
			__( 'reCAPTCHA', 'it-l10n-ithemes-security-pro' ),
			'__return_empty_string',
			'security_page_toplevel_page_itsec_pro'
		);

		//Strong Passwords Fields
		add_settings_field(
			'itsec_recaptcha[enabled]',
			__( 'Enable reCAPTCHA', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_enabled' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-enabled'
		);

		add_settings_field(
			'itsec_recaptcha[site_key]',
			__( 'Site Key', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_site_key' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		add_settings_field(
			'itsec_recaptcha[secret_key]',
			__( 'Secret Key', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_secret_key' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		add_settings_field(
			'itsec_recaptcha[login]',
			__( 'Use on Login', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_login' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		add_settings_field(
			'itsec_recaptcha[register]',
			__( 'Use on New User Registration', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_register' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		add_settings_field(
			'itsec_recaptcha[comments]',
			__( 'Use on Comments', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_comments' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		add_settings_field(
			'itsec_recaptcha[language]',
			__( 'Language', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_language' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		add_settings_field(
			'itsec_recaptcha[theme]',
			__( 'Use Dark Theme', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_theme' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		add_settings_field(
			'itsec_recaptcha[error_threshold]',
			__( 'Lockout Error Threshold', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_error_threshold' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		add_settings_field(
			'itsec_recaptcha[check_period]',
			__( 'Lockout Check Period', 'it-l10n-ithemes-security-pro' ),
			array( $this, 'settings_field_check_period' ),
			'security_page_toplevel_page_itsec_pro',
			'recaptcha-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_pro',
			'itsec_recaptcha',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Prepare and save options in network settings
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function itsec_admin_init_multisite() {

		if ( isset( $_POST['itsec_two_factor'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_pro-options' ) ) {
				die( __( 'Security error!', 'it-l10n-ithemes-security-pro' ) );
			}

			update_site_option( 'itsec_recaptcha', $_POST['itsec_recaptcha'] ); //we must manually save network options

		}

	}

	/**
	 * Adds fields that will be tracked for Google Analytics
	 *
	 * @since 1.14
	 *
	 * @param array $vars tracking vars
	 *
	 * @return array tracking vars
	 */
	public function itsec_tracking_vars( $vars ) {

		$vars['itsec_recaptcha'] = array(
			'enabled'  => '0:b',
			'comments' => '0:b',
			'login'    => '0:b',
		);

		return $vars;

	}

	/**
	 * Render the settings metabox
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function metabox_recaptcha_settings() {

		echo '<p>' . __( 'Protect your site from bots by verifying that the person submitting comments or logging in is indeed human.', 'it-l10n-ithemes-security-pro' ) . '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'recaptcha-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_pro', 'recaptcha-settings', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_pro' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-ithemes-security-pro' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @since 1.14
	 *
	 * @param  Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		$input['enabled']         = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );
		$input['login']           = ( isset( $input['login'] ) && intval( $input['login'] == 1 ) ? true : false );
		$input['comments']        = ( isset( $input['comments'] ) && intval( $input['comments'] == 1 ) ? true : false );
		$input['register']        = ( isset( $input['register'] ) && intval( $input['register'] == 1 ) ? true : false );
		$input['theme']           = ( isset( $input['theme'] ) && intval( $input['theme'] == 1 ) ? true : false );
		$input['check_period']    = isset( $input['check_period'] ) ? absint( $input['check_period'] ) : 5;
		$input['error_threshold'] = isset( $input['error_threshold'] ) ? absint( $input['error_threshold'] ) : 7;
		$input['site_key']        = isset( $input['site_key'] ) ? trim( sanitize_text_field( $input['site_key'] ) ) : '';
		$input['secret_key']      = isset( $input['secret_key'] ) ? trim( sanitize_text_field( $input['secret_key'] ) ) : '';
		$input['language']        = isset( $input['language'] ) ? sanitize_text_field( wp_strip_all_tags( $input['language'] ) ) : '';

		if ( $input['enabled'] === true && ( $input['secret_key'] === '' || $input['site_key'] === '' ) ) {

			$type             = 'error';
			$message          = __( 'You must enter a valid secret key and site key to use the reCAPTCHA feature.', 'it-l10n-ithemes-security-pro' );
			$input['enabled'] = false;

			add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

		}

		if ( is_multisite() ) {

			$this->core->show_network_admin_notice( false );

			$this->settings = $input;

		}

		return $input;

	}

	/**
	 * echos check period Field
	 *
	 * @since 1.14
	 *
	 * @param  array $args field arguments
	 *
	 * @return void
	 */
	public function settings_field_check_period() {

		if ( isset( $this->settings['check_period'] ) ) {

			$check_period = absint( $this->settings['check_period'] );

		} else {

			$check_period = 5;

		}

		echo '<input class="small-text" name="itsec_recaptcha[check_period]" id="itsec_recaptcha_check_period" value="' . $check_period . '" type="text"> ';
		echo '<label for="itsec_recaptcha_check_period"> ' . __( 'Minutes', 'it-l10n-ithemes-security-pro' ) . '</label>';
		echo '<p class="description"> ' . __( 'How long the plugin will remember a bad captcha entry and count it towards a lockout.', 'it-l10n-ithemes-security-pro' ) . '</p>';

	}

	/**
	 * echos reCAPTCHA comments Field
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function settings_field_comments() {

		if ( isset( $this->settings['comments'] ) && $this->settings['comments'] === true ) {

			$comments = 1;

		} else {

			$comments = 0;

		}

		echo '<input type="checkbox" id="itsec_recaptcha_comments" name="itsec_recaptcha[comments]" value="1" ' . checked( 1, $comments, false ) . '/>';
		echo '<label for="itsec_recaptcha_comments"> ' . __( 'Use reCAPTCHA for new comments.', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos Enable reCAPTCHA Field
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function settings_field_enabled() {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {

			$enabled = 1;

		} else {

			$enabled = 0;

		}

		echo '<input type="checkbox" id="itsec_recaptcha_enabled" name="itsec_recaptcha[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		echo '<label for="itsec_recaptcha_enabled"> ' . __( 'Enable reCAPTCHA.', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos Error Threshold Field
	 *
	 * @since 1.14
	 *
	 * @param  array $args field arguments
	 *
	 * @return void
	 */
	public function settings_field_error_threshold() {

		if ( isset( $this->settings['error_threshold'] ) ) {

			$error_threshold = absint( $this->settings['error_threshold'] );

		} else {

			$error_threshold = 7;

		}

		echo '<input class="small-text" name="itsec_recaptcha[error_threshold]" id="itsec_recaptcha_error_threshold" value="' . $error_threshold . '" type="text"> ';
		echo '<label for="itsec_recaptcha_error_threshold"> ' . __( 'Errors', 'it-l10n-ithemes-security-pro' ) . '</label>';
		echo '<p class="description"> ' . __( 'The numbers of failed reCAPTCHA entries that will trigger a lockout. Set to zero (0) to record recaptcha errors without locking out users. This can be useful for troubleshooting content or other errors. The default is 7.', 'it-l10n-ithemes-security-pro' ) . '</p>';

	}

	/**
	 * echos reCAPTCHA language Field
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function settings_field_language() {

		if ( isset( $this->settings['language'] ) ) {

			$language = $this->settings['language'];

		} else {

			$language = '';

		}

		$available_languages = array(
			__( 'Detect', 'it-l10n-ithemes-security-pro' )                  => '',
			__( 'Arabic', 'it-l10n-ithemes-security-pro' )                  => 'ar',
			__( 'Bulgarian', 'it-l10n-ithemes-security-pro' )               => 'bg',
			__( 'Catalan', 'it-l10n-ithemes-security-pro' )                 => 'ca',
			__( 'Chinese (Simplified)', 'it-l10n-ithemes-security-pro' )    => 'zh-CN',
			__( 'Chinese (Traditional)', 'it-l10n-ithemes-security-pro' )   => 'zh-TW',
			__( 'Croation', 'it-l10n-ithemes-security-pro' )                => 'hr',
			__( 'Czech', 'it-l10n-ithemes-security-pro' )                   => 'cs',
			__( 'Danish', 'it-l10n-ithemes-security-pro' )                  => 'da',
			__( 'Dutch', 'it-l10n-ithemes-security-pro' )                   => 'nl',
			__( 'English (UK)', 'it-l10n-ithemes-security-pro' )            => 'en-GB',
			__( 'English (US)', 'it-l10n-ithemes-security-pro' )            => 'en',
			__( 'Filipino', 'it-l10n-ithemes-security-pro' )                => 'fil',
			__( 'Finnish', 'it-l10n-ithemes-security-pro' )                 => 'fi',
			__( 'French', 'it-l10n-ithemes-security-pro' )                  => 'fr',
			__( 'French (Canadian)', 'it-l10n-ithemes-security-pro' )       => 'fr-CA',
			__( 'German', 'it-l10n-ithemes-security-pro' )                  => 'de',
			__( 'German (Austria)', 'it-l10n-ithemes-security-pro' )        => 'de-AT',
			__( 'German (Switzerland)', 'it-l10n-ithemes-security-pro' )    => 'de-CH',
			__( 'Greek', 'it-l10n-ithemes-security-pro' )                   => 'el',
			__( 'Hebrew', 'it-l10n-ithemes-security-pro' )                  => 'iw',
			__( 'Hindi', 'it-l10n-ithemes-security-pro' )                   => 'hi',
			__( 'Hungarian', 'it-l10n-ithemes-security-pro' )               => 'hu',
			__( 'Indonesian', 'it-l10n-ithemes-security-pro' )              => 'id',
			__( 'Italian', 'it-l10n-ithemes-security-pro' )                 => 'it',
			__( 'Japanese', 'it-l10n-ithemes-security-pro' )                => 'ja',
			__( 'Korean', 'it-l10n-ithemes-security-pro' )                  => 'ko',
			__( 'Latvian', 'it-l10n-ithemes-security-pro' )                 => 'lv',
			__( 'Lithuanian', 'it-l10n-ithemes-security-pro' )              => 'lt',
			__( 'Norwegian', 'it-l10n-ithemes-security-pro' )               => 'no',
			__( 'Persian', 'it-l10n-ithemes-security-pro' )                 => 'fa',
			__( 'Polish', 'it-l10n-ithemes-security-pro' )                  => 'pl',
			__( 'Portuguese', 'it-l10n-ithemes-security-pro' )              => 'pt',
			__( 'Portuguese (Brazil)', 'it-l10n-ithemes-security-pro' )     => 'pt-BR',
			__( 'Portuguese (Portugal)', 'it-l10n-ithemes-security-pro' )   => 'pt-PT',
			__( 'Romanian', 'it-l10n-ithemes-security-pro' )                => 'ro',
			__( 'Russian', 'it-l10n-ithemes-security-pro' )                 => 'ru',
			__( 'Serbian', 'it-l10n-ithemes-security-pro' )                 => 'sr',
			__( 'Slovak', 'it-l10n-ithemes-security-pro' )                  => 'sk',
			__( 'Slovenian', 'it-l10n-ithemes-security-pro' )               => 'sl',
			__( 'Spanish', 'it-l10n-ithemes-security-pro' )                 => 'es',
			__( 'Spanish (Latin America)', 'it-l10n-ithemes-security-pro' ) => 'es-419',
			__( 'Swedish', 'it-l10n-ithemes-security-pro' )                 => 'sv',
			__( 'Thai', 'it-l10n-ithemes-security-pro' )                    => 'th',
			__( 'Turkish', 'it-l10n-ithemes-security-pro' )                 => 'tr',
			__( 'Ukranian', 'it-l10n-ithemes-security-pro' )                => 'uk',
			__( 'Vietnamese', 'it-l10n-ithemes-security-pro' )              => 'vi',
		);

		echo '<select name="itsec_recaptcha[language]" id="itsec_recaptcha_language">';

		foreach ( $available_languages as $language_name => $code ) {
			echo '<option value="' . $code . '" ' . selected( $code, $language, false ) . '>' . $language_name . '</option>';
		}

		echo '</select><br>';
		echo '<label for="itsec_recaptcha_language"> ' . __( 'Select the language for the reCAPTCHA box (if autodetect is not working).', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos reCAPTCHA login Field
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function settings_field_login() {

		if ( isset( $this->settings['login'] ) && $this->settings['login'] === true ) {

			$login = 1;

		} else {

			$login = 0;

		}

		echo '<input type="checkbox" id="itsec_recaptcha_login" name="itsec_recaptcha[login]" value="1" ' . checked( 1, $login, false ) . '/>';
		echo '<label for="itsec_recaptcha_login"> ' . __( 'Use reCAPTCHA for user login.', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos reCAPTCHA register Field
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function settings_field_register() {

		if ( isset( $this->settings['register'] ) && $this->settings['register'] === true ) {

			$register = 1;

		} else {

			$register = 0;

		}

		echo '<input type="checkbox" id="itsec_recaptcha_register" name="itsec_recaptcha[register]" value="1" ' . checked( 1, $register, false ) . '/>';
		echo '<label for="itsec_recaptcha_register"> ' . __( 'Use reCAPTCHA for user registration.', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

	/**
	 * echos secret key Field
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function settings_field_secret_key() {

		if ( isset( $this->settings['secret_key'] ) ) {
			$secret_key = sanitize_text_field( $this->settings['secret_key'] );
		} else {
			$secret_key = '';
		}

		echo '<input class="large-text" name="itsec_recaptcha[secret_key]" id="itsec_recaptcha_secret_key" value="' . $secret_key . '" type="text">';
		echo '<label for="itsec_recaptcha_secret_key"> ' . __( 'To use this feature you need a free secret key and secret key from', 'it-l10n-ithemes-security-pro' ) . ' <a href="https://www.google.com/recaptcha/admin" target="_blank">' . __( 'Google reCAPTCHA', 'it-l10n-ithemes-security-pro' ) . '</a>.</label>';
	}

	/**
	 * echos site key Field
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function settings_field_site_key() {

		if ( isset( $this->settings['site_key'] ) ) {
			$site_key = sanitize_text_field( $this->settings['site_key'] );
		} else {
			$site_key = '';
		}

		echo '<input class="large-text" name="itsec_recaptcha[site_key]" id="itsec_recaptcha_site_key" value="' . $site_key . '" type="text">';
		echo '<label for="itsec_recaptcha_site_key"> ' . __( 'To use this feature you need a free site key and secret key from', 'it-l10n-ithemes-security-pro' ) . ' <a href="https://www.google.com/recaptcha/admin" target="_blank">' . __( 'Google reCAPTCHA', 'it-l10n-ithemes-security-pro' ) . '</a>.</label>';
	}

	/**
	 * echos reCAPTCHA theme Field
	 *
	 * @since 1.14
	 *
	 * @return void
	 */
	public function settings_field_theme() {

		if ( isset( $this->settings['theme'] ) && $this->settings['theme'] === true ) {

			$theme = 1;

		} else {

			$theme = 0;

		}

		echo '<input type="checkbox" id="itsec_recaptcha_theme" name="itsec_recaptcha[theme]" value="1" ' . checked( 1, $theme, false ) . '/>';
		echo '<label for="itsec_recaptcha_theme"> ' . __( 'Use dark theme.', 'it-l10n-ithemes-security-pro' ) . '</label>';

	}

}