<?php

/**
 * Manage iThemes Security Pro functionality
 *
 * Provides command line access via WP-CLI: http://wp-cli.org/
 */
class ITSEC_WP_CLI_Command_ITSEC extends WP_CLI_Command {

	/**
	 * Performs a file change scan
	 *
	 * @since 1.12
	 *
	 * @return void
	 */
	public function filescan() {

		if ( ! class_exists( 'ITSEC_File_Change' ) ) {
			WP_CLI::error( __( 'File change scanning is not enabled. You must enable the module first.', 'it-l10n-ithemes-security-pro' ) );
		}

		$module = new ITSEC_File_Change();
		$module->run();

		$response = $module->execute_file_check( false, true );

		if ( ! is_array( $response ) && $response !== false ) { //Response isn't correct, throw an error

			WP_CLI::error( __( 'There was an error in the scan operation. Please check the site logs or contact support.', 'it-l10n-ithemes-security-pro' ) );

		} elseif ( ( isset( $response['added'] ) && sizeof( $response['added'] ) > 0 ) || ( isset( $response['removed'] ) && sizeof( $response['removed'] ) > 0 ) || ( isset( $response['changed'] ) && sizeof( $response['changed'] ) > 0 ) ) { //file changes were detected

			$added    = array();
			$removed  = array();
			$modified = array();

			//process added files if we have them
			if ( isset( $response['added'] ) && sizeof( $response['added'] ) > 0 ) {

				foreach ( $response['added'] as $index => $data ) {

					$added[] = $this->format_filescan( __( 'added', 'it-l10n-ithemes-security-pro' ), $index, $data['h'], $data['d'] );

				}

			}

			//process removed files if we have them
			if ( isset( $response['removed'] ) && sizeof( $response['removed'] ) > 0 ) {

				foreach ( $response['removed'] as $index => $data ) {

					$removed[] = $this->format_filescan( __( 'removed', 'it-l10n-ithemes-security-pro' ), $index, $data['h'], $data['d'] );

				}

			}

			//process modified files if we have them
			if ( isset( $response['changed'] ) && sizeof( $response['changed'] ) > 0 ) {

				foreach ( $response['changed'] as $index => $data ) {

					$modified[] = $this->format_filescan( __( 'modified', 'it-l10n-ithemes-security-pro' ), $index, $data['h'], $data['d'] );

				}

			}

			$file_changes = array_merge( $added, $removed, $modified );

			$obj_type   = 'itsec_file_changes';
			$obj_fields = array(
				'type',
				'file',
				'hash',
				'date',
			);

			$defaults = array(
				'format' => 'table',
				'fields' => array( 'type', 'file', 'hash', 'date', ),
			);

			$formatter = $this->get_formatter( $defaults, $obj_fields, $obj_type );
			$formatter->display_items( $file_changes );

		} else { //no changes detected

			WP_CLI::success( __( 'File scan completed. No changes were detected.', 'it-l10n-ithemes-security-pro' ) );

		}

	}

	/**
	 * Standardize and sanitize output of file changes detected
	 *
	 * @since 1.12
	 *
	 * @param string $type the type of change
	 * @param string $file the file that changed
	 * @param string $hash the md5 hash of the file
	 * @param int    $date the timestamp detected on the file
	 *
	 * @return array presentable array of file information
	 */
	private function format_filescan( $type, $file, $hash, $date ) {

		global $itsec_globals;

		$file_info = array();

		$file = sanitize_text_field( $file );

		$file_info ['type'] = sanitize_text_field( $type );
		$file_info['file']  = substr( $file, strrpos( $file, '/' ) + 1 );
		$file_info['hash']  = substr( sanitize_text_field( $hash ), 0, 8 );
		$file_info['date']  = human_time_diff( $itsec_globals['current_time'], intval( $date ) ) . ' ago';

		return $file_info;

	}

	/**
	 * Returns an instance of the wp-cli formatter for better information dissplay
	 *
	 * @since 1.12
	 *
	 * @param array  $assoc_args array of formatter options
	 * @param array  $obj_fields array of field titles for display
	 * @param string $obj_type   type of object being displayed
	 *
	 * @return \WP_CLI\Formatter
	 */
	private function get_formatter( $assoc_args, $obj_fields, $obj_type ) {

		return new \WP_CLI\Formatter( $assoc_args, $obj_fields, $obj_type );

	}

	/**
	 * Retrieve active lockouts
	 *
	 * @since 1.12
	 *
	 * @return void
	 */
	public function getlockouts() {

		global $itsec_lockout, $itsec_globals;

		$host_locks = $itsec_lockout->get_lockouts( 'host', true );
		$user_locks = $itsec_lockout->get_lockouts( 'user', true );

		if ( empty( $host_locks ) && empty( $user_locks ) ) {

			WP_CLI::success( __( 'There are no current lockouts', 'it-l10n-ithemes-security-pro' ) );

		} else {

			if ( ! empty( $host_locks ) ) {

				foreach ( $host_locks as $index => $lock ) {

					$host_locks[ $index ]['type']           = __( 'host', 'it-l10n-ithemes-security-pro' );
					$host_locks[ $index ]['lockout_expire'] = isset( $lock['lockout_expire'] ) ? human_time_diff( $itsec_globals['current_time'], strtotime( $lock['lockout_expire'] ) ) : __( 'N/A', 'it-l10n-ithemes-security-pro' );

				}

			}

			if ( ! empty( $user_locks ) ) {

				foreach ( $user_locks as $lock ) {

					$host_locks[ $index ]['type']           = __( 'user', 'it-l10n-ithemes-security-pro' );
					$host_locks[ $index ]['lockout_expire'] = isset( $lock['lockout_expire'] ) ? human_time_diff( $itsec_globals['current_time'], strtotime( $lock['lockout_expire'] ) ) : __( 'N/A', 'it-l10n-ithemes-security-pro' );

				}

			}

			$lockouts = array_merge( $host_locks, $user_locks );

			$obj_type   = 'itsec_lockouts';
			$obj_fields = array(
				'ID',
				'type',
				'IP',
				'Username',
				'Expiration',
			);

			$defaults = array(
				'format' => 'table',
				'fields' => array( 'lockout_id', 'type', 'lockout_host', 'lockout_username', 'lockout_expire' ),
			);

			$formatter = $this->get_formatter( $defaults, $obj_fields, $obj_type );
			$formatter->display_items( $lockouts );

		}

	}

	/**
	 * List the most recent log items
	 *
	 * ## OPTIONS
	 *
	 * --count=<ID>
	 * : The numeric ID of the lockout to be released
	 *
	 * @synopsis [<COUNT>] [--count=<COUNT>]
	 *
	 * ## EXAMPLES
	 *
	 *     wp itsec getrecent COUNT
	 *
	 * @since 1.12
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @return void
	 */
	public function getrecent( $args, $assoc_args ) {

		global $itsec_logger, $itsec_globals;

		//make sure they provided a valid ID
		if ( isset( $assoc_args['count'] ) ) {

			$count = intval( $assoc_args['count'] );

		} elseif ( isset( $args[0] ) ) {

			$count = intval( $args[0] );

		} else {

			$count = 10;

		}

		$log_items = $itsec_logger->get_events( 'all', array(), $count, null, 'log_date' );

		if ( ! is_array( $log_items ) || empty( $log_items ) ) {

			WP_CLI::success( __( 'The Security logs are empty.', 'it-l10n-ithemes-security-pro' ) );

		} else {

			foreach ( $log_items as $index => $item ) {

				$log_items[ $index ] = array(
					'Time'     => human_time_diff( $itsec_globals['current_time_gmt'], strtotime( $item['log_date_gmt'] ) ) . ' ' . __( 'ago', 'it-l10n-ithemes-security-pro' ),
					'Type'     => sanitize_text_field( $item['log_function'] ),
					'Priority' => absint( $item['log_priority'] ),
					'IP'       => sanitize_text_field( $item['log_host'] ),
					'Username' => sanitize_text_field( $item['log_username'] ),
					'URL'      => esc_url( $item['log_url'] ),
					'Referror' => esc_url( $item['log_referrer'] ),
				);

			}

			$obj_type   = 'itsec_logs';
			$obj_fields = array(
				__( 'Time', 'it-l10n-ithemes-security-pro' ),
				__( 'Type', 'it-l10n-ithemes-security-pro' ),
				__( 'Priority', 'it-l10n-ithemes-security-pro' ),
				__( 'IP', 'it-l10n-ithemes-security-pro' ),
				__( 'Username', 'it-l10n-ithemes-security-pro' ),
				__( 'URL', 'it-l10n-ithemes-security-pro' ),
				__( 'Referrer', 'it-l10n-ithemes-security-pro' ),
			);

			$defaults = array(
				'format' => 'table',
				'fields' => array( 'Time', 'Type', 'Priority', 'IP', 'Username', 'URL', 'Referrer', ),
			);

			$formatter = $this->get_formatter( $defaults, $obj_fields, $obj_type );
			$formatter->display_items( $log_items );

		}

	}

	/**
	 * Performs a malware scan
	 *
	 * @since 1.12
	 *
	 * @return void
	 */
	public function malwarescan() {

		if ( ! class_exists( 'ITSEC_Malware' ) ) {
			WP_CLI::error( __( 'Malware scanning is not enabled. You must enable the module first.', 'it-l10n-ithemes-security-pro' ) );
		}

		$module = new ITSEC_Malware();
		$module->run();

		$response = $module->one_time_scan();

		$report = $module->scan_report( $response['resource'] );

		if ( ! is_array( $report ) && $report !== false ) { //Response isn't correct, throw an error

			WP_CLI::error( __( 'There was an error in the scan operation. Please check the site logs or contact support.', 'it-l10n-ithemes-security-pro' ) );

		} elseif ( isset( $report['positives'] ) && $report['positives'] > 0 ) { //malware was detected

			WP_CLI::warning( __( 'Issues were detected during the malware scan. Please check the logs for more information.', 'it-l10n-ithemes-security-pro' ) );

		} else { //no changes detected

			WP_CLI::success( __( 'Malware scan completed. No problems were detected.', 'it-l10n-ithemes-security-pro' ) );

		}

	}

	/**
	 * Release a given lockout by ID
	 *
	 * ## OPTIONS
	 *
	 * --id=<ID>
	 * : The numeric ID of the lockout to be released
	 *
	 * @synopsis [<ID>] [--id=<ID>]
	 *
	 * ## EXAMPLES
	 *
	 *     wp itsec releaselockout ID
	 *
	 * @since 1.12
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @return void
	 */
	public function releaselockout( $args, $assoc_args ) {

		global $itsec_lockout;

		$invalid_id = false;

		//make sure they provided a valid ID
		if ( isset( $assoc_args['id'] ) ) {

			$id = intval( $assoc_args['id'] );

		} elseif ( isset( $args[0] ) ) {

			$id = intval( $args[0] );

		} else {

			$invalid_id = true;

		}

		if ( $invalid_id === false ) {

			if ( $itsec_lockout->release_lockout( $id ) !== true ) {
				$invalid_id = true;
			}

		}

		if ( $invalid_id === false ) {

			WP_CLI::success( __( 'The requested lockout has been successfully removed.', 'it-l10n-ithemes-security-pro' ) );

		} else {

			WP_CLI::error( __( 'The requested lockout could not be removed. Please verify the id given is valid. ', 'it-l10n-ithemes-security-pro' ) );

		}

	}

}