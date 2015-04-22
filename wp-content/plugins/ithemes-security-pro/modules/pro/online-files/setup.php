<?php

if ( ! class_exists( 'ITSEC_Online_Files_Setup' ) ) {

	class ITSEC_Online_Files_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'enabled' => false,
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

		}

		/**
		 * Execute module deactivation
		 *
		 * @return void
		 */
		public function execute_deactivate() {

			global $wpdb;

			$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "options` WHERE `option_name` LIKE ('%_itsec_plugin_hashes%')" );
			$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "options` WHERE `option_name` LIKE ('%_itsec_theme_hashes%')" );

			delete_site_transient( 'itsec_online_files_remote_checksums' );
			delete_site_transient( 'itsec_online_files_core_hashes' );

		}

		/**
		 * Execute module uninstall
		 *
		 * @return void
		 */
		public function execute_uninstall() {

			$this->execute_deactivate();
			delete_site_option( 'itsec_online_files' );

		}

		/**
		 * Execute module upgrade
		 *
		 * @return void
		 */
		public function execute_upgrade() {

			global $itsec_old_version;

			if ( $itsec_old_version < 4036 ) {

				delete_site_transient( 'itsec_online_files_remote_checksums' );

			}

		}

	}

}

new ITSEC_Online_Files_Setup();