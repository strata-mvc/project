<?php

if ( ! class_exists( 'ITSEC_Recaptcha_Setup' ) ) {

	class ITSEC_Recaptcha_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'enabled'         => false,
				'login'           => false,
				'comments'        => false,
				'register'        => false,
				'theme'           => false,
				'language'        => '',
				'error_threshold' => 7,
				'check_period'    => 5,
				'site_key'        => '',
				'secret_key'      => '',
			);

			if ( isset( $itsec_setup_action ) ) {

				switch ( $itsec_setup_action ) {

					case 'activate':
						$this->execute_activate();
						break;
					case 'upgrade':
						$this->execute_upgrade();
						break;
					case 'deactivate':
						$this->execute_deactivate();
						break;
					case 'uninstall':
						$this->execute_uninstall();
						break;

				}

			} else {
				wp_die( 'error' );
			}

		}

		/**
		 * Execute module activation.
		 *
		 * @since 1.13
		 *
		 * @return void
		 */
		public function execute_activate() {

			$options = get_site_option( 'itsec_recaptcha' );

			if ( $options === false ) {

				add_site_option( 'itsec_recaptcha', $this->defaults );

			}

		}

		/**
		 * Execute module deactivation
		 *
		 * @since 1.13
		 *
		 * @return void
		 */
		public function execute_deactivate() {
		}

		/**
		 * Execute module uninstall
		 *
		 * @since 1.13
		 *
		 * @return void
		 */
		public function execute_uninstall() {

			$this->execute_deactivate();

			delete_site_option( 'itsec_recaptcha' );

		}

		/**
		 * Execute module upgrade
		 *
		 * @since 1.13
		 *
		 * @return void
		 */
		public function execute_upgrade() {

		}

	}

}

new ITSEC_Recaptcha_Setup();