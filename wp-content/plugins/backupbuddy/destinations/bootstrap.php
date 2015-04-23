<?php
/* Destinations class
 *
 * Handles everything remote destinations and passes onto individual destination
 * class functions.
 *
 * @author Dustin Bolton
 *
 */

class pb_backupbuddy_destinations {

	private $_destination; // Object containing destination.
	private $_settings; // Array of settings for the destination.
	private $_destination_type; // Destination type.
	
	// Default destination information.
	private static $_destination_info_defaults = array(
		'name'			=>		'{Err_3448}',
		'description'	=>		'{Err_4586. Unknown destination type.}',
		'disable_file_management' => '0',
	);
	
	
	
	/* _init_destination()
	 *
	 * Initialize destination, load class, and apply defaults to passed settings.
	 *
	 * @param	array 		$destination_settings		Array of destination settings.
	 * @return	array|false 							Array with key value pairs. Keys: class, settings, info. Bool FALSE on failure.
	 *
	 */
	private static function _init_destination( $destination_settings ) {
		
		pb_backupbuddy::status( 'details', 'Initializing destination.' );
		if ( ( ! isset( $destination_settings['type'] ) ) || ( '' == $destination_settings['type'] ) ) {
			$error = 'Error #8548833: Missing destination settings parameters.';
			echo $error;
			pb_backupbuddy::status( 'error', $error );
			return false;
		}
		
		if ( true !== self::_typePhpSupport( $destination_settings['type'] ) ) {
			pb_backupbuddy::status( 'error', 'Your server does not support this destination. You may need to upgrade to a newer PHP version.' );
			return false;
		}
		
		$destination_type = $destination_settings['type'];
		$destination_class = 'pb_backupbuddy_destination_' . $destination_type;
		
		// Load init file.
		$destination_init_file = pb_backupbuddy::plugin_path() . '/destinations/' . $destination_type . '/init.php';
		pb_backupbuddy::status( 'details', 'Loading destination init file `' . $destination_init_file . '`.' );
		if ( file_exists( $destination_init_file ) ) {
			require_once( $destination_init_file );
		} else {
			pb_backupbuddy::status( 'error', 'Destination type `' . $destination_type . '` init.php file not found. Unable to load class `' . $destination_class . '`.' );
			return false;
		}
		pb_backupbuddy::status( 'details', 'Destination init loaded.' );
		
		if ( ! class_exists( $destination_class ) ) {
			pb_backupbuddy::status( 'error', 'Destination type `' . $destination_type . '` class not found. Unable to load class `' . $destination_class . '`.' );
			return false;
		}
		
		if ( method_exists( $destination_class, 'init' ) ) {
			call_user_func_array( "{$destination_class}::init", array() ); // Initialize.
		}
		
		pb_backupbuddy::status( 'details', 'Initialized `' . $destination_type . '` destination.' );
		
		// Get default settings from class. Was using a variable class name but had to change this for PHP 5.2 compat.
		pb_backupbuddy::status( 'details', 'Applying destination-specific defaults.' );
		$vars = get_class_vars( $destination_class );
		$default_settings = $vars['default_settings'];
		unset( $vars );
		$destination_settings = array_merge( $default_settings, $destination_settings ); // Merge in defaults.
		
		// Get default info from class. Was using a variable class name but had to change this for PHP 5.2 compat.
		pb_backupbuddy::status( 'details', 'Applying global destination defaults.' );
		$vars = get_class_vars( $destination_class );
		$default_info = $vars['destination_info'];
		unset( $vars );
		$destination_info = array_merge( self::$_destination_info_defaults, $default_info ); // Merge in defaults.
		
		return array(
			'class' => $destination_class,
			'settings' => $destination_settings,
			'info' => $destination_info,
		);
		
	} // End _init_destination().
	
	
	
	public static function get_info( $destination_type ) {
		
		// Initialize destination.
		if ( false === ( $destination = self::_init_destination( array( 'type' => $destination_type ) ) ) ) {
			pb_backupbuddy::status( 'warning',  'Unable to load destination `' . $destination_type . '` Some destinations require a newer PHP version.' );
			return false;
		}
		
		return $destination['info'];
		
	} // End get_details().
	
	
	private static function _defaults( $settings ) {
	} // End _defaults().
	
	
	// returns settings form object. false on error.
	// mode = add, edit, or save
	public static function configure( $destination_settings, $mode, $destination_id = '' ) {
		
		//$destination_id = $destination_settings['id']; 
		pb_backupbuddy::status( 'details', 'Configuring destination.' );
		if ( false === ( $destination = self::_init_destination( $destination_settings ) ) ) {
			$error = '{Error #546893498ac. Cannot load destination. This may be due to your PHP version being too old to support this destination (most likely) or its init file is missing.}';
			echo $error;
			pb_backupbuddy::status( 'error', $error );
			return false;
		}
		
		$destination_settings = $destination['settings']; // Settings with defaults applied, normalized, etc.
		//$destination_info = $destination['info'];
		
		$settings_form = new pb_backupbuddy_settings( 'settings', $destination_settings, pb_backupbuddy::ajax_url( 'destination_picker' ) . '&destination_id=' . $destination_id . '&sending=' . pb_backupbuddy::_GET( 'sending' ) );
		$settings_form->add_setting( array(
			'type'		=>		'hidden',
			'name'		=>		'type',
			'value'		=>		$destination_settings['type'],
		) );
		
		$config_file = pb_backupbuddy::plugin_path() . '/destinations/' . $destination_settings['type'] . '/_configure.php';
		pb_backupbuddy::status( 'details', 'Loading destination configure file `' . $config_file . '`.' );
		if ( file_exists( $config_file ) ) {
			require( $config_file );
		} else {
			$error = '{Error #54556543. Missing destination config file `' . $config_file . '`.}';
			echo $error;
			pb_backupbuddy::status( 'error', $error );
			return false;
		}
		
		return $settings_form;
		
	} // End configure().
	
	
	
	// returns settings form object. false on error.
	// mode = add, edit, or save
	public static function manage( $destination_settings, $destination_id = '' ) {
		
		if ( false === ( $destination = self::_init_destination( $destination_settings ) ) ) {
			echo '{Error #546893498ad. Destination configuration file missing.}';
			return false;
		}
		
		$destination_settings = array_merge( self::$_destination_info_defaults, $destination['settings'] ); // Noramlize defaults.
		$destination_info = $destination['info'];
		
		if ( '0' != $destination_settings['disable_file_management'] ) {
			_e( 'Remote file management has been disabled for this destination. Its files cannot be viewed & managed from within BackupBuddy. To re-enable you must create a new destination.', 'it-l10n-backupbuddy' );
			return false;
		}
		
		$manage_file = pb_backupbuddy::plugin_path() . '/destinations/' . $destination_settings['type'] . '/_manage.php';
		if ( file_exists( $manage_file ) ) {
			$destination = &$destination_settings;
			require( $manage_file ); // Incoming variables available to manage file: $destination
			pb_backupbuddy::load_script( 'common' ); // Needed for table 'select all' feature.
			return true;
		} else {
			_e( 'Files stored at this destination cannot be viewed within BackupBuddy.', 'it-l10n-backupbuddy' );
			return false;
		}
		
	} // End manage().
	
	
	
	/* listFiles()
	 *
	 * List all files / directories in a destination.
	 *
	 * @param	array 			$destination_settings		Array of destination settings.
	 * @return	array|false									Array of files on sucess, else bool FALSE.
	 *
	 */
	public static function listFiles( $destination_settings ) {
		
		if ( false === ( $destination = self::_init_destination( $destination_settings ) ) ) {
			echo '{Error #546893498c. Destination configuration file missing.}';
			return false;
		}
		
		$destination_settings = $destination['settings']; // Settings with defaults applied, normalized, etc.
		//$destination_info = $destination['info'];
		$destination_class = $destination['class'];
		
		if ( false === method_exists( $destination_class, 'listFiles' ) ) {
			pb_backupbuddy::status( 'error', 'listFiles destination function called on destination not supporting it.' );
			return false;
		}
		
		return call_user_func_array( "{$destination_class}::listFiles", array( $destination_settings ) );
		
	} // End listFiles().
	
	
	
	/* delete()
	 *
	 * Delete one or more files.
	 *
	 * @param	array 			$destination_settings		Array of destination settings.
	 * @return	bool										true if all deleted, else false if one or more failed to delete.
	 *
	 */
	public static function delete( $destination_settings, $file_or_files ) {
		
		if ( false === ( $destination = self::_init_destination( $destination_settings ) ) ) {
			echo '{Error #546893498f. Destination configuration file missing.}';
			return false;
		}
		
		$destination_settings = $destination['settings']; // Settings with defaults applied, normalized, etc.
		$destination_class = $destination['class'];
		
		if ( false === method_exists( $destination_class, 'delete' ) ) {
			pb_backupbuddy::status( 'error', 'Delete destination function called on destination not supporting it.' );
			return false;
		}
		
		return call_user_func_array( "{$destination_class}::delete", array( $destination_settings, $file_or_files ) );
		
	} // End delete().
	
	
	
	/* getFile()
	 *
	 * Get a remote file and store locally.
	 *
	 * @param	array 			$destination_settings		Array of destination settings.
	 * @param	string			$remote_file				Remote file to retrieve. Filename only. Directory, path, bucket, etc handled in $destination_settings.
	 * @param	string			$local_file					Local file to save to.
	 * @return	bool										true on success, else false.
	 *
	 */
	public static function getFile( $destination_settings, $remote_file, $local_file ) {
		
		$remote_file = basename( $remote_file ); // Sanitize just in case.
		
		if ( false === ( $destination = self::_init_destination( $destination_settings ) ) ) {
			echo '{Error #546893498d. Destination configuration file missing.}';
			return false;
		}
		
		$destination_settings = $destination['settings']; // Settings with defaults applied, normalized, etc.
		//$destination_info = $destination['info'];
		$destination_class = $destination['class'];
		
		if ( false === method_exists( $destination_class, 'getFile' ) ) {
			pb_backupbuddy::status( 'error', 'getFile destination function called on destination not supporting it.' );
			return false;
		}
		
		return call_user_func_array( "{$destination_class}::getFile", array( $destination_settings, $remote_file, $local_file ) );
		
	} // End getFile().
	
	
	
	/*	send()
	 *	
	 *	function description
	 *	
	 *	@param		array			Array of settings to pass to destination.
	 *	@param		array			Array of files to send (full path).
	 *	@return		boolean|array	true success, false on failure, array for multipart send information (transfer is being chunked up into parts).
	 */
	public static function send( $destination_settings, $files, $send_id = '', $delete_after = false ) {
		
		if ( '' != $send_id ) {
			pb_backupbuddy::add_status_serial( 'remote_send-' . $send_id );
			pb_backupbuddy::status( 'details', '----- Initiating master send function. Post-send deletion: ' . $delete_after );
			
			require_once( pb_backupbuddy::plugin_path() . '/classes/fileoptions.php' );
			$fileoptions_file = backupbuddy_core::getLogDirectory() . 'fileoptions/send-' . $send_id . '.txt';
			if ( ! file_exists( $fileoptions_file ) ) {
				//pb_backupbuddy::status( 'details', 'Fileoptions file `' . $fileoptions_file . '` does not exist yet; creating.' );
				//pb_backupbuddy::status( 'details', 'Fileoptions instance #19.' );
				$fileoptions_obj = new pb_backupbuddy_fileoptions( $fileoptions_file, $read_only = false, $ignore_lock = true, $create_file = true );
			} else {
				//pb_backupbuddy::status( 'details', 'Fileoptions file exists; loading.' );
				//pb_backupbuddy::status( 'details', 'Fileoptions instance #18.' );
				$fileoptions_obj = new pb_backupbuddy_fileoptions( $fileoptions_file, $read_only = false, $ignore_lock = false, $create_file = false );
			}
			if ( true !== ( $result = $fileoptions_obj->is_ok() ) ) {
				pb_backupbuddy::status( 'error', __('Fatal Error #9034.2344848. Unable to access fileoptions data.', 'it-l10n-backupbuddy' ) . ' Error: ' . $result );
				return false;
			}
			//pb_backupbuddy::status( 'details', 'Fileoptions data loaded.' );
			$fileoptions = &$fileoptions_obj->options;
			if ( '' == $fileoptions ) {
				$fileoptions = backupbuddy_core::get_remote_send_defaults();
				$fileoptions['type'] = $destination_settings['type'];
				if ( ! is_array( $files ) ) {
					$fileoptions['file'] = $files;
				} else {
					$fileoptions['file'] = $files[0];
				}
				$fileoptions_obj->save();
			}
			
			if ( isset( $fileoptions['status'] ) && ( 'aborted' == $fileoptions['status'] ) ) {
				pb_backupbuddy::status( 'warning', 'Destination send triggered on an ABORTED transfer. Ending send function.' );
				return false;
			}
			
			unset( $fileoptions_obj );
		}
		
		if ( false === ( $destination = self::_init_destination( $destination_settings ) ) ) {
			echo '{Error #546893498a. Destination configuration file missing.}';
			if ( '' != $send_id ) {
				pb_backupbuddy::remove_status_serial( 'remote_send-' . $send_id );
			}
			return false;
		}
		
		$destination_settings = $destination['settings']; // Settings with defaults applied, normalized, etc.
		//$destination_info = $destination['info'];
		
		if ( !is_array( $files ) ) {
			$files = array( $files );
		}
		
		$originalFiles = $files;
		$files_with_sizes = '';
		foreach( $files as $index => $file ) {
			if ( '' == $file ) {
				unset( $files[$index] );
				continue; // Not actually a file to send.
			}
			if ( ! file_exists( $file ) ) {
				pb_backupbuddy::status( 'error', 'Error #58459458743. The file that was attempted to be sent to a remote destination, `' . $file . '`, was not found. It either does not exist or permissions prevent accessing it.' );
				if ( '' != $send_id ) {
					pb_backupbuddy::remove_status_serial( 'remote_send-' . $send_id );
				}
				return false;
			}
			$files_with_sizes .= $file .' (' . pb_backupbuddy::$format->file_size( filesize( $file ) ) . '); ';
		}
		//pb_backupbuddy::status( 'details', 'Sending files `' . $files_with_sizes . '` to destination type `' . $destination_settings['type'] . '` titled `' . $destination_settings['title'] . '`.' );
		unset( $files_with_sizes );
		
		if ( ! method_exists( $destination['class'], 'send' ) ) {
			pb_backupbuddy::status( 'error', 'Destination class `' . $destination['class'] . '` does not support send operation -- missing function.' );
			if ( '' != $send_id ) {
				pb_backupbuddy::remove_status_serial( 'remote_send-' . $send_id );
			}
			return false;
		}
		
		//pb_backupbuddy::status( 'details', 'Calling send function.' );
		
		//$result = $destination_class::send( $destination_settings, $files );
		global $pb_backupbuddy_destination_errors;
		$pb_backupbuddy_destination_errors = array();
		$result = call_user_func_array( "{$destination['class']}::send", array( $destination_settings, $files, $send_id, $delete_after ) );
		if ( $result === false ) {
			$error_details = implode( '; ', $pb_backupbuddy_destination_errors );
			if ( '' != $error_details ) {
				$error_details = ' Details: ' . $error_details;
			}
			
			$log_directory = backupbuddy_core::getLogDirectory();
			
			$preError = 'There was an error sending to the remote destination. One or more files may have not been fully transferred. Please see error details for additional information. If the error persists, enable full error logging and try again for full details and troubleshooting. Details: ' . "\n\n";
			$logFile = $log_directory . 'status-remote_send-' . $send_id . '_' . pb_backupbuddy::$options['log_serial'] . '.txt';
			pb_backupbuddy::status( 'details', 'Looking for remote send log file `' . $logFile . '`.' );
			if ( ! file_exists( $logFile ) ) {
				pb_backupbuddy::status( 'details', 'Remote send log file not found.' );
				backupbuddy_core::mail_error( $preError . $error_details );
			} else { // Log exists. Attach.
				pb_backupbuddy::status( 'details', 'Remote send log file found. Attaching to error email.' );
				backupbuddy_core::mail_error( $preError . $error_details . "\n\nSee the attached log for details.", '', array( $logFile ) );
			}
			
			// Save error details into fileoptions for this send.
			//pb_backupbuddy::status( 'details', 'About to load fileoptions data.' );
			require_once( pb_backupbuddy::plugin_path() . '/classes/fileoptions.php' );
			pb_backupbuddy::status( 'details', 'Fileoptions instance #45.' );
			$fileoptions_obj = new pb_backupbuddy_fileoptions( backupbuddy_core::getLogDirectory() . 'fileoptions/send-' . $send_id . '.txt', $read_only = false, $ignore_lock = false, $create_file = false );
			if ( true !== ( $fileoptions_result = $fileoptions_obj->is_ok() ) ) {
				pb_backupbuddy::status( 'error', __('Error #9034.32731. Unable to access fileoptions data.', 'it-l10n-backupbuddy' ) . ' Error: ' . $fileoptions_result );
			}
			//pb_backupbuddy::status( 'details', 'Fileoptions data loaded.' );
			$fileoptions = &$fileoptions_obj->options;
			$fileoptions['error'] = 'Error sending.' . $error_details;
			$fileoptions_obj->save();
			unset( $fileoptions_obj );
			
		}
		
		
		if ( is_array( $result ) ) { // Send is multipart.
			pb_backupbuddy::status( 'details', 'Multipart chunk mode completed a pass of the send function. Resuming will be needed. Result: `' . print_r( $result, true ) . '`.' );
			if ( '' != $send_id ) {
				
				//pb_backupbuddy::status( 'details', 'About to load fileoptions data.' );
				require_once( pb_backupbuddy::plugin_path() . '/classes/fileoptions.php' );
				//pb_backupbuddy::status( 'details', 'Fileoptions instance #17.' );
				$fileoptions_obj = new pb_backupbuddy_fileoptions( backupbuddy_core::getLogDirectory() . 'fileoptions/send-' . $send_id . '.txt', $read_only = false, $ignore_lock = false, $create_file = false );
				if ( true !== ( $fileoptions_result = $fileoptions_obj->is_ok() ) ) {
					pb_backupbuddy::status( 'error', __('Fatal Error #9034.387462. Unable to access fileoptions data.', 'it-l10n-backupbuddy' ) . ' Error: ' . $fileoptions_result );
					return false;
				}
				//pb_backupbuddy::status( 'details', 'Fileoptions data loaded.' );
				$fileoptions = &$fileoptions_obj->options;
				
				$fileoptions['_multipart_status'] = $result[1];
				pb_backupbuddy::status( 'details', 'Destination debugging details: `' . print_r( $fileoptions, true ) . '`.' );
				$fileoptions_obj->save();
				unset( $fileoptions_obj );
				pb_backupbuddy::status( 'details', 'Next multipart chunk will be processed shortly. Now waiting on its cron...' );
			}
		} else { // Single all-at-once send.
			if ( false === $result ) {
				pb_backupbuddy::status( 'details', 'Completed send function. Failure. Post-send deletion will be skipped if enabled.' );
			} elseif ( true === $result ) {
				pb_backupbuddy::status( 'details', 'Completed send function. Success.' );
			} else {
				pb_backupbuddy::status( 'warning', 'Completed send function. Unknown result: `' . $result . '`.' );
			}
			
			if ( ( true === $delete_after ) && ( false !== $result ) ) { // If set to delete after and was not a send failure.
				pb_backupbuddy::status( 'details', __( 'Post-send deletion enabled.', 'it-l10n-backupbuddy' ) );
				if ( false === $result ) {
					pb_backupbuddy::status( 'details', 'Skipping post-send deletion since transfer failed.' );
				} else {
					pb_backupbuddy::status( 'details', 'Performing post-send deletion since transfer succeeded.' );
					
					pb_backupbuddy::status( 'details', 'About to load fileoptions data.' );
					require_once( pb_backupbuddy::plugin_path() . '/classes/fileoptions.php' );
					pb_backupbuddy::status( 'details', 'Fileoptions instance #16.' );
					$fileoptions_obj = new pb_backupbuddy_fileoptions( backupbuddy_core::getLogDirectory() . 'fileoptions/send-' . $send_id . '.txt', $read_only = false, $ignore_lock = false, $create_file = false );
					if ( true !== ( $fileoptions_result = $fileoptions_obj->is_ok() ) ) {
						pb_backupbuddy::status( 'error', __('Error #9034.387462. Unable to access fileoptions data.', 'it-l10n-backupbuddy' ) . ' Error: ' . $fileoptions_result );
					}
					pb_backupbuddy::status( 'details', 'Fileoptions data loaded.' );
					$fileoptions = &$fileoptions_obj->options;
					array_push( $originalFiles, $fileoptions['file'] ); // Add file (maybe multipart) into files to clean up.
					foreach( $originalFiles as $file ) {
						pb_backupbuddy::status( 'details', 'Deleting local file `' . $file . '`.' );
						// Handle post-send deletion on success.
						if ( file_exists( $file ) ) {
							$unlink_result = @unlink( $file );
							if ( true !== $unlink_result ) {
								pb_backupbuddy::status( 'error', 'Unable to unlink local file `' . $file . '`.' );
							}
						}
						if ( file_exists( $file ) ) { // File still exists.
							pb_backupbuddy::status( 'details', __('Error. Unable to delete local file `' . $file .'` after send as set in settings.', 'it-l10n-backupbuddy' ) );
							backupbuddy_core::mail_error( 'BackupBuddy was unable to delete local file `' . $file . '` after successful remove transfer though post-remote send deletion is enabled. You may want to delete it manually. This can be caused by permission problems or improper server configuration.' );
						} else { // Deleted.
							pb_backupbuddy::status( 'details', __('Deleted local archive after successful remote destination send based on settings.', 'it-l10n-backupbuddy' ) );
							pb_backupbuddy::status( 'archiveDeleted', '' );
						}
					}
				}
			} else {
				pb_backupbuddy::status( 'details', 'Post-send deletion not enabled.' );
			}
		}
		
		// NOTE: Call this before removing status serial so it shows in log.
		//pb_backupbuddy::status( 'details', 'Finishing send() function.' );
		
		if ( '' != $send_id ) {
			pb_backupbuddy::remove_status_serial( 'remote_send-' . $send_id );
		}
		
		return $result;
		
	} // End send().
	
	
	// return true on success, else error message.
	public static function test( $destination_settings ) {
		
		if ( false === ( $destination = self::_init_destination( $destination_settings ) ) ) {
			echo '{Error #546893498ab. Destination configuration file missing.}';
			return false;
		}
		
		$destination_settings = $destination['settings']; // Settings with defaults applied, normalized, etc.
		//$destination_info = $destination['info'];
		
		$destination_type = $destination_settings['type'];
		$destination_class = 'pb_backupbuddy_destination_' . $destination_type;
		
		// test() returns true on success, else error message.
		$result = call_user_func_array( "{$destination_class}::test", array( $destination_settings ) );
		
		return $result;
	} // End test().
	
	
	
	// Just pass through.
	public static function multipart_cleanup( $destination_settings ) {
		
		if ( false === ( $destination = self::_init_destination( $destination_settings ) ) ) {
			echo '{Error #546893498d. Destination configuration file missing.}';
			return false;
		}
		
		$destination_settings = $destination['settings']; // Settings with defaults applied, normalized, etc.
		//$destination_info = $destination['info'];
		
		$destination_type = $destination_settings['type'];
		$destination_class = 'pb_backupbuddy_destination_' . $destination_type;
		
		// just pass through whatever response
		return call_user_func_array( "{$destination_class}::multipart_cleanup", array( $destination_settings ) );
		
	} // End test().
	
	
	
	/*	shutdown_function()
	 *	
	 *	Used for catching fatal PHP errors during backup to write to log for debugging.
	 *	
	 *	@return		null
	 */
	public static function shutdown_function() {
		//error_log ('shutdown_function()');
		
		// Get error message.
		// Error types: http://php.net/manual/en/errorfunc.constants.php
		$e = error_get_last();
		if ( $e === NULL ) { // No error of any kind.
			return;
		} else { // Some type of error.
			if ( !is_array( $e ) || ( $e['type'] != E_ERROR ) && ( $e['type'] != E_USER_ERROR ) ) { // Return if not a fatal error.
				return;
			}
		}
		
		
		// Calculate log directory.
		$log_directory = backupbuddy_core::getLogDirectory(); // Also handles when importbuddy.
		$main_file = $log_directory . 'log-' . pb_backupbuddy::$options['log_serial'] . '.txt';
		
		
		// Determine if writing to a serial log.
		if ( pb_backupbuddy::$_status_serial != '' ) {
			$serial = pb_backupbuddy::$_status_serial;
			$serial_file = $log_directory . 'status-' . $serial . '_' . pb_backupbuddy::$options['log_serial'] . '.txt';
			$write_serial = true;
		} else {
			$write_serial = false;
		}
		
		
		// Format error message.
		$e_string = "---\n" . __( 'Fatal PHP error encountered:', 'it-l10n-backupbuddy' ) . "\n";
		foreach( (array)$e as $e_line_title => $e_line ) {
			$e_string .= $e_line_title . ' => ' . $e_line . "\n";
		}
		$e_string .= "---\n";
		
		
		// Write to log.
		file_put_contents( $main_file, $e_string, FILE_APPEND );
		if ( $write_serial === true ) {
			@file_put_contents( $serial_file, $e_string, FILE_APPEND );
		}
		
		
	} // End shutdown_function.
	
	
	
	/* _typePhpSuport()
	 *
	 * Does this server's PHP support this destination type?
	 *
	 * @param		string			Name of destination type / class / directory.
	 * @return		bool|string		true success, else PHP version minimum (unsupported destination)
	 *
	 */
	private static function _typePhpSupport( $destination_type ) {
		
		$destinations_root = dirname( __FILE__ ) . '/';
		if ( file_exists( $destinations_root . $destination_type . '/_phpmin.php' ) ) {
			$php_minimum = file_get_contents( $destinations_root . $destination_type . '/_phpmin.php' );
			if ( version_compare( PHP_VERSION, $php_minimum, '<' ) ) { // Server's PHP is insufficient.
				return $php_minimum;
			}
		}
		return true;
		
	} // End _typePhpSupport().
	
	
	
	/* get_destinations_list()
	 *
	 * Return an array of remote destinations. By default only gives those compatible with this server.
	 *
	 * @param	bool	$showUnabailable	Whether or not to return destinations that are incompatible with this server. Default: false.
	 * @return	array 						Array of destination information. 'compatible' key bool whether or not it is compatible with this server ('compatibility' key includes server settings required if incompatible). incompatible destinations not shown by default.
	 *
	 */
	public static function get_destinations_list( $showUnavailable = false ) {
		$destinations_root = dirname( __FILE__ ) . '/';
		
		$destination_dirs = glob( $destinations_root . '*', GLOB_ONLYDIR );
		if ( !is_array( $destination_dirs ) ) {
			$destination_dirs = array();
		}
		
		$destination_list = array();
		foreach( $destination_dirs as $destination_dir ) {
			$destination_dir = str_replace( $destinations_root, '', $destination_dir );
			if ( substr( $destination_dir, 0, 1 ) == '_' ) { // Skip destinations beginning in underscore as they are not an actual destination.
				continue;
			}
			
			$destination = self::get_info( $destination_dir );
			$destination['compatible'] = true;
			$destination['type'] = $destination_dir;
			
			if ( true !== ( $phpmin = self::_typePhpSupport( $destination_dir ) ) ) { // Compatibility failed. Skip this destination.
				if ( TRUE !== $showUnavailable ) {
					continue;
				} else {
					$destination['compatible'] = false;
					$destination['name'] = $destination_dir;
					$destination['compatibility'] = __( 'Requires PHP v', 'it-l10n-backupbuddy' ) . $phpmin;
				}
			}
			
			/*
			if ( 'site' == basename( $destination_dir ) ) {
				if ( ! defined( 'BACKUPBUDDY_DEV' ) || ( true !== BACKUPBUDDY_DEV ) ) {
					continue;
				}
			}
			*/
			
			$destination_list[$destination_dir] = $destination;
		}
		
		// Change some ordering.
		$stash_destination = array( 'stash' => $destination_list['stash'] );
		unset( $destination_list['stash'] );
		
		$deploy_destination = array();
		if ( isset( $destination_list['site'] ) ) {
			$deploy_destination = array( 'site' => $destination_list['site'] );
			unset( $destination_list['site'] );
		}
		
		$s3_destination = array( 's3' => $destination_list['s3'] );
		unset( $destination_list['s3'] );
		
		$destination_list = array_merge( $stash_destination, $deploy_destination, $s3_destination, $destination_list );
		
		
		return $destination_list;
	} // End get_destinations().
	
	
	// Handles removing destination from schedules also.
	// True on success, else error message.
	public static function delete_destination( $destination_id, $confirm = false ) {
		
		if ( $confirm === false ) {
			return 'Error #54858597. Not deleted. Confirmation parameter missing.';
		}
		
		// Delete destination.
		unset( pb_backupbuddy::$options['remote_destinations'][$destination_id] );
		
		// Remove this destination from all schedules using it.
		foreach( pb_backupbuddy::$options['schedules'] as $schedule_id => $schedule ) {
			$remote_list = '';
			$trimmed_destination = false;
			
			$remote_destinations = explode( '|', $schedule['remote_destinations'] );
			foreach( $remote_destinations as $remote_destination ) {
				if ( $remote_destination == $destination_id ) {
					$trimmed_destination = true;
				} else {
					$remote_list .= $remote_destination . '|';
				}
			}
			
			if ( $trimmed_destination === true ) {
				pb_backupbuddy::$options['schedules'][$schedule_id]['remote_destinations'] = $remote_list;
			}
		} // end foreach.
		
		pb_backupbuddy::save();
		return true;

	} // End delete_destination().
	
}


