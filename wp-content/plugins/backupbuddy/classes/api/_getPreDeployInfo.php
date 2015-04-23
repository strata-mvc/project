<?php
$sha1 = false; // Whether to calculate sha1 hash for determining file differences.



$upload_max_filesize = str_ireplace( 'M', '', @ini_get( 'upload_max_filesize' ) );
if ( ( ! is_numeric( $upload_max_filesize ) ) || ( 0 == $upload_max_filesize ) ) {
	$upload_max_filesize = 1;
}

$max_execution_time = str_ireplace( 's', '', @ini_get( 'max_execution_time' ) );
if ( ( ! is_numeric( $max_execution_time ) ) || ( 0 == $max_execution_time ) ) {
	$max_execution_time = 30;
}

$memory_limit = str_ireplace( 'M', '', @ini_get( 'memory_limit' ) );
if ( ( ! is_numeric( $memory_limit ) ) || ( 0 == $memory_limit ) ) {
	$memory_limit = 32;
}

$max_post_size = str_ireplace( 'M', '', @ini_get( 'post_max_size' ) );
if ( ( ! is_numeric( $max_post_size ) ) || ( 0 == $max_post_size ) ) {
	$max_post_size = 8;
}



$dbTables = array();
global $wpdb;
$rows = $wpdb->get_results( "SHOW TABLE STATUS", ARRAY_A );
foreach( $rows as $row ) {
	
	// Hide BackupBuddy temp tables.
	if ( 'BBold-' == substr( $row['Name'], 0, 6 ) ) {
		continue;
	}
	if ( 'BBnew-' == substr( $row['Name'], 0, 6 ) ) {
		continue;
	}
	
	$dbTables[] = $row['Name'];
}



// search backwards starting from haystack length characters from the end
function backupbuddy_startsWith($haystack, $needle) {
	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
} // End backupbuddy_startsWith().



/* backupbuddy_hashGlob()
 *
 * Calculate comparison data for all files within a path. Useful for tracking file changes between two locations.
 *
 */
function backupbuddy_hashGlob( $root, $generate_sha1 = false, $excludes = array() ) {
	
	//$root = rtrim( $root, '/\\' );
	$files = (array) pb_backupbuddy::$filesystem->deepglob( $root );
	$root_len = strlen( $root );
	$hashedFiles = array();
	foreach( $files as $file_id => &$file ) {
		$new_file = substr( $file, $root_len );
		
		// If this file/directory begins with an exclusion then jump to next file/directory.
		foreach( $excludes as $exclude ) {
			if ( backupbuddy_startsWith( $new_file, $exclude ) ) {
				continue 2;
			}
		}
		
		// Omit directories themselves.
		if ( is_dir( $file ) ) {
			continue;
		}
		
		$stat = stat( $file );
		if ( FALSE === $stat ) { pb_backupbuddy::status( 'error', 'Unable to read file `' . $file . '` stat.' ); }
		
		$hashedFiles[$new_file] = array(
			'size'		=> $stat['size'],
			'modified'	=> $stat['mtime'],
		);
		if ( ( true === $generate_sha1 ) && ( $stat['size'] < 1073741824 ) ) { // < 100mb
			$hashedFiles[$new_file]['sha1'] = sha1_file( $file );
		}
		unset( $files[$file_id] ); // Better to free memory or leave out for performance?
		
	}
	unset( $files );
	
	return $hashedFiles;
	
} // End backupbuddy_hashGlob.



// List
function backupbuddy_dbMediaSince( $includeThumbs = true ) {
	global $wpdb;
	$wpdb->show_errors(); // Turn on error display.
	
	$mediaFiles = array();
	
	$sql = "select " . $wpdb->prefix . "postmeta.meta_value as file," . $wpdb->prefix . "posts.post_modified as file_modified," . $wpdb->prefix . "postmeta.meta_key as meta_key from " . $wpdb->prefix . "postmeta," . $wpdb->prefix . "posts WHERE ( meta_key='_wp_attached_file' OR meta_key='_wp_attachment_metadata' ) AND " . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.id ORDER BY meta_key ASC";
	$results = $wpdb->get_results( $sql, ARRAY_A );
	if ( ( null === $results ) || ( false === $results ) ) {
		pb_backupbuddy::status( 'error', 'Error #238933: Unable to calculate media with query `' . $sql . '`. Check database permissions or contact host.' );
	}
	foreach( (array)$results as $result ) {
		
		if ( $result['meta_key'] == '_wp_attached_file' ) {
			$mediaFiles[ $result['file'] ] = array(
				'modified'	=> $result['file_modified']
			);
		}
		
		if ( true === $includeThumbs ) {
			if ( $result['meta_key'] == '_wp_attachment_metadata' ) {
				$data = unserialize( $result['file'] );
				foreach( $data['sizes'] as $size ) {
					$mediaFiles[ $size['file'] ] = array(
						'modified'	=> $mediaFiles[ $data['file'] ]['modified']
					);
				}
			}
		}
		
	}
	unset( $results );
	return $mediaFiles;
}



// Get list of active plugins and remove BackupBuddy from it so we don't update any BackupBuddy files when deploying. Could cause issues with the API replacing files mid-deploy.
$activePlugins = backupbuddy_api::getActivePlugins();
foreach( $activePlugins as $activePluginIndex => $activePlugin ) {
	if ( false !== strpos( $activePlugin['name'], 'BackupBuddy' ) ) {
		unset( $activePlugins[ $activePluginIndex ] );
	}
}
$activePluginDirs = array();
foreach( $activePlugins as $activePluginDir => $activePlugin ) {
	$activePluginDirs[] = dirname( WP_PLUGIN_DIR . '/' . $activePluginDir );
}
$allPluginDirs = glob( WP_PLUGIN_DIR . '/*', GLOB_ONLYDIR );
$inactivePluginDirs = array_diff( $allPluginDirs, $activePluginDirs ); // Remove active plugins from directories of all plugins to get directories of inactive plugins to exclude later.
$inactivePluginDirs[] = pb_backupbuddy::plugin_path(); // Also exclude BackupBuddy directory.



$upload_dir = wp_upload_dir();
$mediaExcludes = array(
	'/backupbuddy_backups',
	'/pb_backupbuddy',
	'/backupbuddy_temp',
);
$mediaSignatures = backupbuddy_hashGlob( $upload_dir['basedir'], $sha1, $mediaExcludes );



global $wp_version;
return array(
	'backupbuddyVersion'		=> pb_backupbuddy::settings( 'version' ),
	'wordpressVersion'			=> $wp_version,
	'localTime'					=> time(),
	'php'						=> array(
									'upload_max_filesize' => $upload_max_filesize,
									'max_execution_time' => $max_execution_time,
									'memory_limit' => $memory_limit,
									'max_post_size' => $max_post_size,
									),
	'abspath'					=> ABSPATH,
	'siteurl'					=> site_url(),
	'homeurl'					=> home_url(),
	'tables'					=> $dbTables,
	'dbPrefix'					=> $wpdb->prefix,
	'activePlugins'				=> $activePlugins,
	'activeTheme'				=> get_template(),
	'themeSignatures'			=> backupbuddy_hashGlob( get_template_directory(), $sha1 ),
	'pluginSignatures'			=> backupbuddy_hashGlob( WP_PLUGIN_DIR . '/', $sha1, $inactivePluginDirs ),
	'mediaSignatures'			=> $mediaSignatures,
	'mediaCount'				=> count( $mediaSignatures ),
	'notifications'				=> array(), // Array of string notification messages.
);

