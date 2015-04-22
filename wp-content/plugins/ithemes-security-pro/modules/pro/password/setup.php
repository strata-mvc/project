<?php

if ( ! class_exists( 'ITSEC_Password_Setup' ) ) {

	class ITSEC_Password_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'enabled'         => false,
				'generate'        => true,
				'generate_role'   => 'administrator',
				'generate_length' => 50,
				'expire'          => false,
				'expire_force'    => false,
				'expire_max'      => 120,
				'expire_role'     => 'administrator',
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

			$options = get_site_option( 'itsec_password' );

			if ( $options === false ) {

				add_site_option( 'itsec_password', $this->defaults );

			}

		}

		/**
		 * Execute module deactivation
		 *
		 * @return void
		 */
		public function execute_deactivate() {

			delete_metadata( 'user', null, 'itsec_password_change_required', null, true );

		}

		/**
		 * Execute module uninstall
		 *
		 * @return void
		 */
		public function execute_uninstall() {

			$this->execute_deactivate();

			delete_site_option( 'itsec_password' );
			delete_metadata( 'user', null, 'itsec_last_password_change', null, true );

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

new ITSEC_Password_Setup();