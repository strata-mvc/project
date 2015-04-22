<?php
if ( ! defined( 'PB_IMPORTBUDDY' ) || ( true !== PB_IMPORTBUDDY ) ) {
	die( '<html></html>' );
}
Auth::require_authentication(); // Die if not logged in.


// Tests variables to populate with results.
$tests = array(
	'connect'				=> false,	// Able to connect & login to db server?
	'connect_error'			=> '',		// mysql error message in response to connect & login (if any).
	'selectdb'				=> false,	// Able to select the database?
	'selectdb_error'		=> '',		// mysql error message in response to selecting (if any).
	'createdroptable'		=> false,	// ability to CREATE a new table (and delete it).
	'createdroptable_error'	=> '',		// create table mysql error (if any).
	'prefix'				=> false,	// Whether or not prefix meets the bare minimum to be accepted.
	'prefix_exists'			=> true,	// WordPress tables matching prefix found?
	'prefix_warn'			=> true,	// Warn if prefix of a bad format.
	'overall_error'			=> '',		// Overall error of the test. If missing fields then this will be what errors about missing field(s).
);

$server = pb_backupbuddy::_POST( 'server' );
$username = pb_backupbuddy::_POST( 'username' );
$password = pb_backupbuddy::_POST( 'password' );
$database = pb_backupbuddy::_POST( 'database' );
$prefix = pb_backupbuddy::_POST( 'prefix' );

if ( ( '' == $server ) || ( '' == $username ) || ( '' == $database ) || ( '' == $prefix ) ) {
	$tests['overall_error'] = 'One or more database settings was left blank. All fields except optional password are required.';
	die( json_encode( $tests ) );
}



/***** BEGIN TESTS *****/

if ( false === @mysql_connect( $server, $username, $password ) ) { // CONNECT failed.
	
	$tests['connect_error'] = mysql_error() . ' - ErrorNo: `' . mysql_errno() . '`.';
	
} else { // CONNECT success.
	
	$tests['connect'] = true;
	
	if ( false === @mysql_select_db( $database ) ) { // SELECT failed.
		
		$tests['selectdb_error'] = mysql_error() . ' - ErrorNo: `' . mysql_errno() . '`.';
		
	} else { // SELECT success.
		
		$tests['selectdb'] = true;
		
		// Test ability to create (and delete) a table to verify permissions.
		@mysql_query("DROP TABLE `" . mysql_real_escape_string( $prefix ) . "buddy_test`"); // drop just in case a prior attempt failed.
		$result = mysql_query( "CREATE TABLE `" . mysql_real_escape_string( $prefix ) . "buddy_test` (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY)" );
		if ( false !== $result ) { // create success.
			// Drop temp test table we created before we declare success.
			$result = mysql_query("DROP TABLE `" . mysql_real_escape_string( $prefix ) . "buddy_test`");
			if ( false !== $result ) { // drop success.
				$tests['createdroptable'] = true;
			} else { // drop fail.
				$tests['createdroptable_error'] = 'Unable to delete temporary table. ' . mysql_error() . ' - ErrorNo: `' . mysql_errno() . '`.';
			}
		} else { // create fail.
			$tests['createdroptable_error'] = 'Unable to create temporary table. ' . mysql_error() . ' - ErrorNo: `' . mysql_errno() . '`.';
		}
		
		// WordPress tables exist matching prefix?
		$result = mysql_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( str_replace( '_', '\_', $prefix ) . "%" ) . "'" );
		if ( mysql_num_rows( $result ) == 0 ) { // WordPress EXISTS already. Collision.
			$tests['prefix_exists'] = false;
		}
		unset( $result );
		
	} // end select success.
	
} // end connect success.




if ( ! preg_match('|[^a-z0-9_]|i', $prefix ) ) { // Prefix meets WP minimum.
	$tests['prefix'] = true;
	 if ( preg_match('/^[a-z0-9]+_$/i', $prefix ) ) { // Prefix passes with no warning.
		$tests['prefix_warn'] = false;
	}
}


/***** END TESTS *****/


die( json_encode( $tests ) );
