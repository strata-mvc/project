<?php

if ( ! class_exists( 'ITSEC_Two_Factor_Setup' ) ) {

	class ITSEC_Two_Factor_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'enabled' => false,
				'roll'    => 'administrator',
				'offset'  => 1,
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
		 * @since 4.0
		 *
		 * @return void
		 */
		public function execute_activate() {

			$options = get_site_option( 'itsec_two_factor' );

			if ( $options === false ) {

				add_site_option( 'itsec_two_factor', $this->defaults );

			}

		}

		/**
		 * Execute module deactivation
		 *
		 * @return void
		 */
		public function execute_deactivate() {
		}

		/**
		 * Execute module uninstall
		 *
		 * @return void
		 */
		public function execute_uninstall() {

			$this->execute_deactivate();

			delete_site_option( 'itsec_two_factor' );

			delete_metadata( 'user', null, 'itsec_two_factor_enabled', null, true );
			delete_metadata( 'user', null, 'itsec_two_factor_description', null, true );
			delete_metadata( 'user', null, 'itsec_two_factor_key', null, true );
			delete_metadata( 'user', null, 'itsec_two_factor_use_app', null, true );
			delete_metadata( 'user', null, 'itsec_two_factor_app_pass', null, true );
			delete_metadata( 'user', null, 'itsec_two_factor_last_login', null, true );
			delete_metadata( 'user', null, 'itsec_two_factor_override', null, true );
			delete_metadata( 'user', null, 'itsec_two_factor_override_expires', null, true );

		}

		/**
		 * Execute module upgrade
		 *
		 * @return void
		 */
		public function execute_upgrade() {

		}

	}

}

new ITSEC_Two_Factor_Setup();