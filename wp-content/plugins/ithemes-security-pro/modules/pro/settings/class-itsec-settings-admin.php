<?php

/*
Version 2.0.0

Version History
	2.0.0 - 2015-03-19 - Chris Jean
		Bug Fix: Fixed how autoload settings would not be respected due to use of booleans rather than 'yes' or 'no' strings.
		Enhancement: Rewrote import code to properly handle import files even when they are renamed, to validate the data before attempting to import it, to have a wider range of temporary directory options, to provide error messages specific to each failure condition, and to clean up any created files or directories.
		Enhancement: Added relevant status messages when exporting or importing settings.
*/


class ITSEC_Settings_Admin {

	private
		$core,
		$settings;

	function run( $core ) {

		$this->settings    = true;
		$this->core        = $core;
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'itsec_add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'itsec_admin_init' ) ); //initialize admin area

	}

	/**
	 * Export all plugin settings and push to user.
	 *
	 * @since 4.5
	 *
	 * @return mixed file or false
	 */
	private function export_settings() {

		global $wpdb, $itsec_globals;

		$ignored_settings = array( //Array of settings that should not be exported
			'itsec_local_file_list',
			'itsec_jquery_version',
			'itsec_initials',
			'itsec_data',
		);

		$raw_items = $wpdb->get_results( "SELECT * FROM `" . $wpdb->options . "` WHERE `option_name` LIKE 'itsec%';", ARRAY_A );

		$clean_items = array();

		//Loop through raw options to make sure serialized data is output as a JSON array (don't want to have to unserialize anything from the user later).
		foreach ( $raw_items as $item ) {

			if ( ! in_array( $item['option_name'], $ignored_settings ) ) {

				$clean_items[] = array(
					'name'  => $item['option_name'],
					'value' => maybe_unserialize( $item['option_value'] ),
					'auto'  => ( $item['autoload'] === 'yes' ? 'yes' : 'no' ),
				);

			}

		}

		$content = json_encode( $clean_items ); //encode the PHP array of settings

		$settings_file = '/itsec_options.json';
		$zip_file      = '/itsec_options.zip';

		if ( ! file_put_contents( $itsec_globals['ithemes_dir'] . $settings_file, $content, LOCK_EX ) ) {

			$message = __( 'We could not create the backup file. If the problem persists contact support', 'it-l10n-ithemes-security-pro' );

			add_settings_error( 'itsec', 'settings_updated', $message, 'error' );

			return;

		}

		//Attempt to zip the saved file
		if ( ! class_exists( 'PclZip' ) ) {
			require( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
		}

		@chdir( $itsec_globals['ithemes_dir'] );
		$zip = new PclZip( './' . $zip_file );

		if ( $zip->create( './' . $settings_file ) == 0 ) {

			$message = __( 'We could not create the backup file. If the problem persists contact support', 'it-l10n-ithemes-security-pro' );

			add_settings_error( 'itsec', 'settings_updated', $message, 'error' );

		}

		@unlink( './' . $settings_file ); //Delete the original

		//Send the settings to the given user and then delete the file
		$user = trim( $_POST['email_address'] );

		if ( is_email( $user ) !== false ) {

			$attachment = array( './' . $zip_file );
			$body       = __( 'Attached is the settings file for ', 'it-l10n-ithemes-security-pro' ) . ' ' . get_option( 'siteurl' ) . __( ' created at', 'it-l10n-ithemes-security-pro' ) . ' ' . date( 'l, F jS, Y \a\\t g:i a', $itsec_globals['current_time'] );

			//Setup the remainder of the email
			$subject = __( 'Security Settings File', 'it-l10n-ithemes-security-pro' ) . ' ' . date( 'l, F jS, Y \a\\t g:i a', $itsec_globals['current_time'] );
			$subject = apply_filters( 'itsec_backup_email_subject', $subject );
			$headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";

			//Use HTML Content type
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

			if ( defined( 'ITSEC_DEBUG' ) && ITSEC_DEBUG === true ) {
				$body .= '<p>' . __( 'Debug info (source page): ' . esc_url( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ) ) . '</p>';
			}

			$mail_success = wp_mail( $user, $subject, '<html>' . $body . '</html>', $headers, $attachment );

			//Remove HTML Content type
			remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

			if ( $mail_success === false ) {

				$message = __( 'We could not send the email. You will need to retrieve the backup file manually.', 'it-l10n-ithemes-security-pro' );

				add_settings_error( 'itsec', 'settings_updated', $message, 'error' );

			} else {
				@unlink( './' . $zip_file );
			}
		}
		
		
		add_settings_error( 'itsec', 'export_successful', sprintf( __( 'The export was created successfully. Please check %s for the export file.', 'it-l10n-ithemes-security-pro' ), $user ), 'updated' );
	}
	
	/**
	 * Ensure that a specific entry in $_FILES is present and valid.
	 *
	 * @param string $name The name of the $_FILES index to check.
	 * @return bool|WP_Error Returns true if the requested entry is present and valid, or a WP_Error object containing an error message otherwise.
	 */
	private function validate_uploaded_file( $name ) {
		if ( ! isset( $_FILES[$name] ) ) {
			return new WP_Error( 'file_upload_field_missing', __( 'The form field used to upload the file is missing. This could indicate a browser or plugin compatibility issue. Please contact support.', 'it-l10n-ithemes-security-pro' ) );
		}
		
		
		$file = $_FILES[$name];
		
		if ( isset( $file['error'] ) && ( UPLOAD_ERR_OK !== $file['error'] ) ) {
			$messages = array(
				UPLOAD_ERR_INI_SIZE   => __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.' ),
				UPLOAD_ERR_FORM_SIZE  => __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.' ),
				UPLOAD_ERR_PARTIAL    => __( 'The uploaded file was only partially uploaded.' ),
				UPLOAD_ERR_NO_FILE    => __( 'No file was uploaded.' ),
				UPLOAD_ERR_NO_TMP_DIR => __( 'Missing a temporary folder.' ),
				UPLOAD_ERR_CANT_WRITE => __( 'Failed to write file to disk.' ),
				UPLOAD_ERR_EXTENSION  => __( 'File upload stopped by extension.' ),
			);
			
			if ( isset( $messages[$file['error']] ) ) {
				$message = $messages[$file['error']];
			} else {
				$message = sprintf( __( 'Unknown upload error (code "%s")', 'it-l10n-ithemes-security-pro' ), $file['error'] );
			}
			
			return new WP_Error( 'file_upload_error', $message );
		}
		
		if ( ! isset( $file['tmp_name'] ) ) {
			return new WP_Error( 'file_upload_php_error', __( 'The uploaded file was unable to be read due to a PHP error. The "tmp_name" field for the file upload is missing. Please contact support.', 'it-l10n-ithemes-security-pro' ) );
		}
		
		
		return true;
	}
	
	/**
	 * Get a writable temporary directory.
	 *
	 * The directory has a randomized name to make it hard for snooping people/bots to find the location. Multiple
	 * directories to house the temporary directory are checked in order to ensure that a usable directory can be
	 * created on as many platforms as possible.
	 *
	 * @uses ITSEC_Settings_Admin::get_writable_subdir() to get the generated random directory.
	 *
	 * @return string|WP_Error Returns the path to the temporary directory
	 */
	private function get_temp_dir() {
		global $itsec_globals;
		
		
		if ( ! empty( $itsec_globals['ithemes_dir'] ) ) {
			if ( ! is_dir( $itsec_globals['ithemes_dir'] ) ) {
				@mkdir( $itsec_globals['ithemes_dir'] );
				
				if ( false !== ( $handle = @fopen( $itsec_globals['ithemes_dir'] . '/.htaccess', 'w' ) ) ) {
					@fwrite( $handle, 'Deny from all' );
					@fclose( $handle );
				}
			}
			
			if ( is_dir( $itsec_globals['ithemes_dir'] ) ) {
				if ( ! file_exists( "{$itsec_globals['ithemes_dir']}/index.php" ) ) {
					file_put_contents( "{$itsec_globals['ithemes_dir']}/index.php", "<?php\n// Silence is golden." );
				}
				
				if ( false !== ( $dir = $this->get_writable_subdir( $itsec_globals['ithemes_dir'] ) ) ) {
					return $dir;
				}
			}
		}
		
		
		$uploads_dir_data = wp_upload_dir();
		
		if ( false !== ( $dir = $this->get_writable_subdir( $uploads_dir_data['basedir'] ) ) ) {
			return $dir;
		}
		if ( false !== ( $dir = $this->get_writable_subdir( $uploads_dir_data['path'] ) ) ) {
			return $dir;
		}
		if ( false !== ( $dir = $this->get_writable_subdir( ABSPATH ) ) ) {
			return $dir;
		}
		if ( is_callable( 'sys_get_temp_dir' ) ) {
			if ( false !== ( $dir = $this->get_writable_subdir( @sys_get_temp_dir() ) ) ) {
				return $dir;
			}
		} else {
			if ( false !== ( $dir = $this->get_writable_subdir( getenv( 'TMP' ) ) ) ) {
				return $dir;
			}
			if ( false !== ( $dir = $this->get_writable_subdir( getenv( 'TEMP' ) ) ) ) {
				return $dir;
			}
			if ( false !== ( $dir = $this->get_writable_subdir( getenv( 'TMPDIR' ) ) ) ) {
				return $dir;
			}
		}
		if ( false !== ( $dir = $this->get_writable_subdir( dirname( __FILE__ ) ) ) ) {
			return $dir;
		}
		
		return new WP_Error( 'cannot_create_temp_dir', __( 'Unable to create a temporary directory. This indicates a file permissions issue where the web server user cannot create files or directories. Please correct the file permission issue or contact your host for assistance and then try again.', 'it-l10n-ithemes-security-pro' ) );
	}
	
	/**
	 * Returns a writable, randomized directory if one can be created in the supplied directory
	 *
	 * @param string $dir Directory path to create the randomized directory in.
	 * @return string|bool Returns the path to the writable directory, or false if it cannot be created.
	 */
	private function get_writable_subdir( $dir ) {
		if ( empty( $dir ) ) {
			return false;
		}
		if ( ! is_dir( $dir ) ) {
			return false;
		}
		
		$test_file = @tempnam( $dir, 'itsec-temp-' );
		
		if ( false === $test_file ) {
			return false;
		}
		if ( false === @unlink( $test_file ) ) {
			return false;
		}
		
		$subdir = $test_file;
		
		if ( false === @mkdir( $subdir, 0700 ) ) {
			return false;
		}
		if ( ! is_writable( $subdir ) ) {
			@rmdir( $subdir );
			return false;
		}
		
		return $subdir;
	}
	
	/**
	 * Returns validated iThemes Security settings the supplied JSON file.
	 *
	 * @param string $file File path to the JSON file to pull the settings from.
	 * @return array|WP_Error Returns an array of valid iThemes Security settings, or a WP_Error object otherwise.
	 */
	private function get_settings_from_json_file( $file ) {
		$file_contents = file_get_contents( $file );
		
		if ( false === $file_contents ) {
			return new WP_Error( 'unable_to_read_settings_file', __( 'The settings file cannot be read. This could indicate a temporary problem on the server. Please try again.', 'it-l10n-ithemes-security-pro' ) );
		}
		
		
		$data = json_decode( $file_contents, true );
		
		if ( is_null( $data ) && ( 'null' !== $file_contents ) ) {
			return new WP_Error( 'unable_to_decode_json_data', __( 'The settings file is invalid or corrupt. The JSON data was unable to be read. Please ensure that you are supplying a valid export file in either a zip or JSON format.', 'it-l10n-ithemes-security-pro' ) );
		}
		
		if ( ! is_array( $data ) ) {
			return new WP_Error( 'found_non_array_json_data', __( 'The settings file contains invalid data. The data is expected to be in a JSON array format, but a different format was found. Please ensure that you are supplying a valid export file.', 'it-l10n-ithemes-security-pro' ) );
		}
		
		
		foreach ( $data as $index => $setting ) {
			if ( ! isset( $setting['name'] ) || ! isset( $setting['value'] ) || ! isset( $setting['auto'] ) ) {
				return new WP_Error( 'invalid_data_format', __( 'The settings file contains invalid data. Valid exported settings are a series of options table entries. The supplied data did not match this format. Please ensure that you are supplying a valid export file.', 'it-l10n-ithemes-security-pro' ) );
			}
			
			if ( 'itsec' !== substr( $setting['name'], 0, 5 ) ) {
				return new WP_Error( 'non_security_settings_found', __( 'The settings file contains settings that are not for iThemes Security. These settings will not be imported. Please supply an export file from iThemes Security.', 'it-l10n-ithemes-security-pro' ) );
			}
			
			
			if ( is_bool( $setting['auto'] ) ) {
				$data[$index]['auto'] = ( $setting['auto'] ) ? 'yes' : 'no';
			}
		}
		
		return $data;
	}
	
	/**
	 * Returns the iThemes Security settings contained in the supplied file path.
	 *
	 * The supplied file can be a zip file or a JSON file.
	 *
	 * @uses ITSEC_Settings_Admin::get_settings_from_json_file() to parse the JSON file.
	 *
	 * @param string $file File path for the file to pull iThemes Security settings from.
	 * @return array|WP_Error Returns an array of options settings on success, or a WP_Error object on failure.
	 */
	private function get_settings_from_file( $file, $type ) {
		$temp_dir = $this->get_temp_dir();
		
		if ( ! is_wp_error( $temp_dir ) ) {
			WP_Filesystem();
			
			$unzip_result = unzip_file( $file, $temp_dir );
			
			if ( true === $unzip_result ) {
				$files = $this->get_files_from_directory( $temp_dir );
				
				if ( is_wp_error( $files ) ) {
					$this->delete_directory( $temp_dir );
					
					return new WP_Error( $files->get_error_code(), sprintf( __( 'A server issue is preventing the zip file data from being read. Please unzip the export file and try importing the contained JSON file. The specific error that prevented the zip file data from being read is as follows: %s', 'it-l10n-ithemes-security-pro' ), $files->get_error_message() ) );
				}
				
				foreach ( $files as $file ) {
					$result = $this->get_settings_from_json_file( $file );
					
					if ( ! is_wp_error( $result ) ) {
						if ( isset( $settings ) ) {
							$this->delete_directory( $temp_dir );
							
							return new WP_Error( 'multiple_settings_files_found', __( 'The supplied zip file contained more than one JSON file with valid iThemes Security settings. Only zip files with one JSON file of valid settings are permitted. Please ensure that a valid export file is supplied.', 'it-l10n-ithemes-security-pro' ) );
						}
						
						$settings = $result;
					}
				}
				
				$this->delete_directory( $temp_dir );
				
				if ( isset( $settings ) ) {
					return $settings;
				} else {
					return new WP_Error( 'valid_json_settings_file_not_found', __( 'The supplied zip file did not contain a JSON file with valid iThemes Security settings. Please ensure that a valid export file is supplied.', 'it-l10n-ithemes-security-pro' ) );
				}
			}
		}
		
		if ( ! is_wp_error( $temp_dir ) ) {
			$this->delete_directory( $temp_dir );
		}
		
		
		$json_result = $this->get_settings_from_json_file( $file );
		
		if ( ! is_wp_error( $json_result ) ) {
			return $json_result;
		}
		
		
		if ( ( '.zip' === substr( $file, -4 ) ) || ( false !== strpos( $type, 'zip' ) ) ) {
			unset( $error );
			
			if ( is_wp_error( $temp_dir ) ) {
				$error = $temp_dir;
			}
			if ( is_wp_error( $unzip_result ) ) {
				$error = $unzip_result;
			}
			
			if ( isset( $error ) ) {
				return new WP_Error( $error->get_error_code(), sprintf( __( 'The unzip utility built into WordPress reported the following error when trying to unzip the supplied file: %s', 'it-l10n-ithemes-security-pro' ), $error->get_error_message() ) );
			}
		}
		
		return $json_result;
	}
	
	/**
	 * Import settings provided by user.
	 *
	 * @return void
	 */
	private function import_settings() {
		global $itsec_globals;
		
		check_admin_referer( 'ITSEC_admin_save', 'wp_nonce' );
		
		
		$result = $this->validate_uploaded_file( 'settings_file' );
		
		if ( is_wp_error( $result ) ) {
			$error = $result;
		} else {
			$type = isset( $_FILES['settings_file']['type'] ) ? $_FILES['settings_file']['type'] : '';
			$settings = $this->get_settings_from_file( $_FILES['settings_file']['tmp_name'], $type );
			
			if ( is_wp_error( $settings ) ) {
				$error = $settings;
			}
		}
		
		@unlink( $_FILES['settings_file']['tmp_name'] );
		
		if ( isset( $error ) ) {
			$message = sprintf( __( 'Unable to import settings. %1$s (code: %2$s)', 'it-l10n-ithemes-security-pro' ), $error->get_error_message(), $error->get_error_code() );
			add_settings_error( 'itsec', 'import_error', $message );
			
			return;
		}
		
		
		foreach ( $settings as $setting ) {
			if ( is_multisite() ) {
				delete_site_option( $setting['name'] );
				add_site_option( $setting['name'], $setting['value'] );
			} else {
				delete_option( $setting['name'] );
				add_option( $setting['name'], $setting['value'], null, $setting['auto'] );
			}
		}
		
		
		add_settings_error( 'itsec', 'import_success', __( 'Settings successfully imported.', 'it-l10n-ithemes-security-pro' ), 'updated' );
	}
	
	/**
	 * Execute admin initializations
	 *
	 * @return void
	 */
	public function itsec_admin_init() {
		if ( $this->settings === true && isset( $_POST['itsec_import_settings'] ) && $_POST['itsec_import_settings'] === 'itsec_import_settings' ) {
			if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'ITSEC_admin_save' ) ) {
				die( __( 'Security check', 'it-l10n-ithemes-security-pro' ) );
			}
			
			$this->import_settings();
		}
		
		if ( $this->settings === true && isset( $_POST['itsec_export_settings'] ) && $_POST['itsec_export_settings'] === 'itsec_export_settings' ) {
			if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'ITSEC_admin_save' ) ) {
				die( __( 'Security check', 'it-l10n-ithemes-security-pro' ) );
			}
			
			$this->export_settings();
		}
	}
	
	/**
	 * Add meta boxes to primary options pages
	 *
	 * @return void
	 */
	public function itsec_add_admin_meta_boxes() {

		$id    = 'settings_options';
		$title = __( 'Settings Import and Export', 'it-l10n-ithemes-security-pro' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_settings' ),
			'security_page_toplevel_page_itsec_advanced',
			'advanced',
			'core'
		);

	}

	/**
	 * Render the settings metabox
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_settings() {

		global $itsec_globals;

		echo '<p>' . __( 'Have more than one site? Want to just backup your settings for later?', 'it-l10n-ithemes-security-pro' ) . '</p>';
		echo '<p>' . __( 'Use the buttons below to import and export your iThemes Security settings ', 'it-l10n-ithemes-security-pro' ) . '</p>';
		echo '<p>' . __( 'Please note that if you are migrating a site to a different server you will have to update any path settings such as logs or backup files after the import.', 'it-l10n-ithemes-security-pro' ) . '</p>';

		$user = wp_get_current_user();

		?>

		<form method="post" action="?page=toplevel_page_itsec_advanced&settings-updated=true" class="itsec-form">

			<?php wp_nonce_field( 'ITSEC_admin_save', 'wp_nonce' ); ?>

			<input type="hidden" name="itsec_export_settings" value="itsec_export_settings">

			<table class="form-table">
				<tr valign="top" id="settings_import_field">
					<th scope="row" class="settinglabel">
						<label for="itsec_settings_input"><?php _e( 'Email Address', 'it-l10n-ithemes-security-pro' ); ?></label>
					</th>
					<td class="settingfield">
						<?php //username field ?>
						<input id="itsec_settings_input" name="email_address" type="text"
						       value="<?php echo $user->user_email; ?>" required/>

						<p class="description"><?php echo __( 'Enter the email address to send the file to. It will also be saved to', 'it-l10n-ithemes-security-pro' ) . '<strong>' . $itsec_globals['ithemes_dir'] . '</strong> ' . __( 'which must be accessed manually (you cannot access the file via your web browser for security reasons).', 'it-l10n-ithemes-security-pro' ); ?></p>
					</td>
				</tr>
			</table>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Export Settings', 'it-l10n-ithemes-security-pro' ); ?>"/>
			</p>
		</form>

		<hr/>

		<form method="post" enctype="multipart/form-data"
		      action="?page=toplevel_page_itsec_advanced&settings-updated=true" class="itsec-form">

			<?php wp_nonce_field( 'ITSEC_admin_save', 'wp_nonce' ); ?>

			<input type="hidden" name="itsec_import_settings" value="itsec_import_settings">

			<table class="form-table">
				<tr valign="top" id="settings_import_field">
					<th scope="row" class="settinglabel">
						<label for="itsec_settings_input"><?php _e( 'Select Settings File', 'it-l10n-ithemes-security-pro' ); ?></label>
					</th>
					<td class="settingfield">
						<?php //username field ?>
						<input id="itsec_settings_input" name="settings_file" type="file" value="" required/>

						<p class="description"><?php _e( 'Select a settings file for import.', 'it-l10n-ithemes-security-pro' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Import Settings', 'it-l10n-ithemes-security-pro' ); ?>"/>
			</p>
		</form>

	<?php

	}

	/**
	 * Set HTML content type for email
	 *
	 * @return string html content type
	 */
	public function set_html_content_type() {
		return 'text/html';
	}
	
	/**
	 * An is_callable() function which also checks ini configs that can disable functions.
	 *
	 * @return bool Returns true if the function is callable, or false otherwise.
	 */
	private function is_callable_function( $function ) {
		if ( ! is_callable( $function ) ) {
			return false;
		}
		
		$disabled_functions = preg_split( '/\s*,\s*/', (string) ini_get( 'disable_functions' ) );
		
		if ( in_array( $function, $disabled_functions ) ) {
			return false;
		}
		
		$disabled_functions = preg_split( '/\s*,\s*/', (string) ini_get( 'suhosin.executor.func.blacklist' ) );
		
		if ( in_array( $function, $disabled_functions ) ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns a file listing for the supplied directory.
	 *
	 * @param string $dir Directory path to return file listing for.
	 * @param bool $include_dirs Set to true to include directories in the file listing. Default false.
	 * @return array|WP_Error Returns an array containing the file listing, or a WP_Error object otherwise.
	 */
	private function get_files_from_directory( $dir, $include_dirs = false ) {
		if ( ! is_dir( $dir ) ) {
			return new WP_Error( 'directory_does_not_exist', __( 'Attempted to search for files in a directory that does not exist.', 'it-l10n-ithemes-security-pro' ) );
		}
		
		$glob_is_callable = $this->is_callable_function( 'glob' );
		$opendir_is_callable = $this->is_callable_function( 'opendir' );
		
		if ( ! $glob_is_callable && ! $opendir_is_callable ) {
			return new WP_Error( 'glob_and_opendir_disabled', __( 'Unable to scan for files due to server restrictions that disabled both the glob() and opendir() functions.', 'it-l10n-ithemes-security-pro' ) );
		}
		
		if ( $glob_is_callable ) {
			$visible_files = glob( "$dir/*" );
			$hidden_files = glob( "$dir/.*" );
			
			if ( ( false !== $visible_files ) || ( false !== $hidden_files ) ) {
				$files = array();
				
				if ( false !== $visible_files ) {
					$files = array_merge( $files, $visible_files );
				}
				if ( false !== $hidden_files ) {
					$files = array_merge( $files, $hidden_files );
				}
			}
		}
		
		if ( ! isset( $files ) ) {
			if ( false === ( $dh = opendir( $dir ) ) ) {
				return new WP_Error( 'unable_to_search_directories', __( 'Unable to scan for files as both the glob() and opendir() functions return errors.', 'it-l10n-ithemes-security-pro' ) );
			}
			
			$files = array();
			
			while ( false !== ( $file = readdir( $dh ) ) ) {
				$files[] = $file;
			}
			
			closedir( $dh );
		}
		
		
		$contents = array();
		
		foreach ( (array) $files as $file ) {
			if ( in_array( basename( $file ), array( '.', '..' ) ) ) {
				continue;
			}
			
			if ( $include_dirs || is_file( $file ) ) {
				$contents[] = $file;
			}
		}
		
		return $contents;
	}
	
	/**
	 * Recursively removes the specified directory and its contents.
	 *
	 * @param string $dir Directory path to be removed.
	 * @return bool Returns true if the directory no longer exists, or false otherwise.
	 */
	private function delete_directory( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return true;
		}
		
		$files = $this->get_files_from_directory( $dir, true );
		
		if ( is_wp_error( $files ) ) {
			return false;
		}
		
		$contents = array();
		
		foreach ( (array) $files as $file ) {
			if ( in_array( basename( $file ), array( '.', '..' ) ) ) {
				continue;
			}
			
			if ( is_dir( $file ) ) {
				$this->delete_directory( $file );
			} else if ( is_file( $file ) ) {
				@unlink( $file );
			}
		}
		
		@rmdir( $dir );
		
		if ( ! is_dir( $dir ) ) {
			return true;
		}
		
		return false;
	}
}
