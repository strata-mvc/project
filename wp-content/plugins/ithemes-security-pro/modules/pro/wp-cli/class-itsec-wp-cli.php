<?php

class ITSEC_WP_ClI {

	function run() {

		if ( class_exists( 'WP_CLI_Command' ) ) { //make sure wp-cli is present

			//Load temporary whitelist command
			if ( ! class_exists( 'ITSEC_WP_CLI_Command_ITSEC' ) ) {

				require( dirname( __FILE__ ) . '/class-itsec-wp-cli-command-itsec.php' );
				WP_CLI::add_command( 'itsec', 'ITSEC_WP_CLI_Command_ITSEC' );

			}

		}

	}

}