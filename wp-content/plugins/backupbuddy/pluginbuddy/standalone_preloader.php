<?php
$pb_styles = array();
$pb_scripts = array();
$pb_actions = array();
$wp_scripts = array();

// NOTE: Modified from WP to rtrim on dirname() due to Windows issues.
function site_url() {
	$pageURL = 'http';
	if ( isset( $_SERVER["HTTPS"] ) && ( $_SERVER["HTTPS"] == "on" ) ) {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"] . rtrim( dirname($_SERVER['PHP_SELF']), '/\\' );
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"] . rtrim( dirname($_SERVER['PHP_SELF']), '/\\' );
	}
	
	return $pageURL;
}

/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @since 2.0.0
 *
 * @param array|string $value The array or string to be stripped.
 * @return array|string Stripped array (or string in the callback).
 */
function stripslashes_deep($value) {
	if ( is_array($value) ) {
		$value = array_map('stripslashes_deep', $value);
	} elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = stripslashes_deep( $data );
		}
	} else {
		$value = stripslashes($value);
	}

	return $value;
}


/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 * Courtesy WordPress; since WordPress 2.0.5.
 *
 * @param mixed $data Value to check to see if was serialized.
 * @return bool False if not serialized and true if it was.
 */
function is_serialized( $data ) {
	// if it isn't a string, it isn't serialized
	if ( ! is_string( $data ) )
		return false;
	$data = trim( $data );
 	if ( 'N;' == $data )
		return true;
	$length = strlen( $data );
	if ( $length < 4 )
		return false;
	if ( ':' !== $data[1] )
		return false;
	$lastc = $data[$length-1];
	if ( ';' !== $lastc && '}' !== $lastc )
		return false;
	$token = $data[0];
	switch ( $token ) {
		case 's' :
			if ( '"' !== $data[$length-2] )
				return false;
		case 'a' :
		case 'O' :
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b' :
		case 'i' :
		case 'd' :
			return (bool) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
	}
	return false;
} // End is_serialized().

function __( $text, $domain = '' ) {
	return $text;
}
function _e( $text, $domain = '' ) {
	echo $text;
}

function wp_style_is( $name ) {
	global $pb_styles;
	return array_key_exists( $name, $pb_styles );
}
function wp_enqueue_style( $name, $file, $deps = array(), $ver = '' ) {
	global $pb_styles;
	$pb_styles[$name]['file'] = $file;
	$pb_styles[$name]['version'] = $ver;
	$pb_styles[$name]['printed'] = false;
}
function wp_print_styles( $name ) {
	global $pb_styles;
	if ( $pb_styles[$name]['printed'] === false ) {
		$pb_styles[$name]['printed'] = true;
		
		echo '<link rel="stylesheet" type="text/css" href="' . $pb_styles[$name]['file'] . '?ver=' . $pb_styles[$name]['version'] . '">';
	}
}

function wp_script_is( $name ) {
	global $pb_scripts;
	return array_key_exists( $name, $pb_scripts );
}
function wp_enqueue_script( $name, $file, $deps = array(), $ver = '' ) {
	global $pb_scripts;
	$pb_scripts[$name]['file'] = $file;
	$pb_scripts[$name]['version'] = $ver;
	$pb_scripts[$name]['printed'] = false;
}
function wp_print_scripts( $name ) {
	global $pb_scripts;
	if ( $pb_scripts[$name]['printed'] === false ) {
		$pb_scripts[$name]['printed'] = true;
		
		echo '<script src="' . $pb_scripts[$name]['file'] . '?ver=' . $pb_scripts[$name]['version'] . '" type="text/javascript"></script>';
	}
}

function add_action( $tag, $callback ) {
	global $pb_actions;
	$pb_actions[$tag]['callback'] = $callback;
}


function is_admin() {
	return true;
}

function apply_filters( $filter, $value ) {
	return $value;
}

function _cleanup_header_comment($str) {
	return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
}

function get_plugin_data( $plugin_file, $markup = true, $translate = true ) {

	$default_headers = array(
		'Name' => 'Plugin Name',
		'PluginURI' => 'Plugin URI',
		'Version' => 'Version',
		'Description' => 'Description',
		'Author' => 'Author',
		'AuthorURI' => 'Author URI',
		'TextDomain' => 'Text Domain',
		'DomainPath' => 'Domain Path',
		'Network' => 'Network',
		// Site Wide Only is deprecated in favor of Network.
		'_sitewide' => 'Site Wide Only',
	);

	$plugin_data = get_file_data( $plugin_file, $default_headers, 'plugin' );

	// Site Wide Only is the old header for Network
	if ( empty( $plugin_data['Network'] ) && ! empty( $plugin_data['_sitewide'] ) ) {
		_deprecated_argument( __FUNCTION__, '3.0', sprintf( __( 'The <code>%1$s</code> plugin header is deprecated. Use <code>%2$s</code> instead.' ), 'Site Wide Only: true', 'Network: true' ) );
		$plugin_data['Network'] = $plugin_data['_sitewide'];
	}
	$plugin_data['Network'] = ( 'true' == strtolower( $plugin_data['Network'] ) );
	unset( $plugin_data['_sitewide'] );

	//For backward compatibility by default Title is the same as Name.
	$plugin_data['Title'] = $plugin_data['Name'];

	if ( $markup || $translate )
		$plugin_data = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup, $translate );
	else
		$plugin_data['AuthorName'] = $plugin_data['Author'];

	return $plugin_data;
}


function get_file_data( $file, $default_headers, $context = '' ) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 8192 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	if ( $context != '' ) {
		$extra_headers = apply_filters( "extra_{$context}_headers", array() );

		$extra_headers = array_flip( $extra_headers );
		foreach( $extra_headers as $key=>$value ) {
			$extra_headers[$key] = $key;
		}
		$all_headers = array_merge( $extra_headers, (array) $default_headers );
	} else {
		$all_headers = $default_headers;
	}

	foreach ( $all_headers as $field => $regex ) {
		preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, ${$field});
		if ( !empty( ${$field} ) )
			${$field} = _cleanup_header_comment( ${$field}[1] );
		else
			${$field} = '';
	}

	$file_data = compact( array_keys( $all_headers ) );

	return $file_data;
}


function wp_nonce_field() {
	return;
}



// Some PHP installs don't have ngettext. Needed by human_time_diff().
if ( ! function_exists( 'ngettext' ) ) {
	function ngettext( $singular, $plural, $num ) {
		if ( $num > 1 ) {
			return $plural;
		} else {
			return $singular;
		}
	}
} // End ngettext().



/**
 * Determines the difference between two timestamps.
 *
 * The difference is returned in a human readable format such as "1 hour",
 * "5 mins", "2 days".
 *
 * @since 1.5.0
 *
 * @param int $from Unix timestamp from which the difference begins.
 * @param int $to Optional. Unix timestamp to end the time difference. Default becomes time() if not set.
 * @return string Human readable time difference.
 */
function human_time_diff( $from, $to = '' ) {
	if ( empty($to) )
		$to = time();
	$diff = (int) abs($to - $from);
	if ($diff <= 3600) {
		$mins = round($diff / 60);
		if ($mins <= 1) {
			$mins = 1;
		}
		/* translators: min=minute */
		$since = sprintf( ngettext( '%s min', '%s mins', $mins ), $mins);
	} else if (($diff <= 86400) && ($diff > 3600)) {
		$hours = round($diff / 3600);
		if ($hours <= 1) {
			$hours = 1;
		}
		$since = sprintf( ngettext('%s hour', '%s hours', $hours ), $hours);
	} elseif ($diff >= 86400) {
		$days = round($diff / 86400);
		if ($days <= 1) {
			$days = 1;
		}
		$since = sprintf( ngettext('%s day', '%s days', $days ), $days);
	}
	return $since;
}



/**
 * Unserialize value only if it was serialized.
 *
 * @since 2.0.0
 *
 * @param string $original Maybe unserialized original, if is needed.
 * @return mixed Unserialized data can be any type.
 */
function maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
		return @unserialize( $original );
	return $original;
}






// NOT IMPLEMENTED BUT NON-BLOCKING.

function register_activation_hook() {
}
function load_plugin_textdomain() {
}
function current_user_can( $role ) {
	return true;
}
function get_temp_dir() {
	
	if ( function_exists('sys_get_temp_dir') ) {
		$temp = sys_get_temp_dir();
		if ( @is_dir( $temp ) && is_writable( $temp ) )
			return rtrim( $temp, '/\\' ) . '/';
	}
	
	$temp = ABSPATH . 'temp/';
	@mkdir( $temp );
	if ( is_dir( $temp ) && is_writable( $temp ) ) {
		return $temp;
	}
	
	$temp = '/tmp/';
	@mkdir( $temp );
	return $temp;
}


function wp_upload_dir() {
	return array( 'basedir' => ABSPATH );
}



function wp_die( $message ) {
	pb_backupbuddy::status( 'error', 'wp_die() called with message: ' . $message );
	echo $message;
	die();
}

function wp_load_translations_early() {
}

function wp_debug_backtrace_summary() {
}

if ( !defined('WP_DEBUG') )
	define( 'WP_DEBUG', false );
if ( !defined('WP_DEBUG_DISPLAY') )
	define( 'WP_DEBUG_DISPLAY', true );
if ( !defined('WP_DEBUG_LOG') )
	define('WP_DEBUG_LOG', false);
if ( !defined('WP_CACHE') )
	define('WP_CACHE', false);

function admin_url( $val ) {
	return '?' . $val;
}


?>