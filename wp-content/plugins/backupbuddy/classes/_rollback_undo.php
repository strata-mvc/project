<?php
/* BackupBuddy script to undo a database rollback procedure if it has failed.
 * Access this script in your web browser to undo a rollback.
 *
 * @author Dustin Bolton, January 2014.
 * @url http://ithemes.com
 *
 * NOTES:
 * 	-- This will only seek out wp-config.php in the current DIR or up one level. If this file is not in the root then it is innert.
 * 	-- No user-editable variables within. No user-submitted data is used for any processing.
 */

// DIE if accessing this file directly in BackupBuddy.
if ( false === stripos( basename( __FILE__ ), '-' ) ) {
	die();
}
?>

<style>
	body {
		font-family: "Open Sans",sans-serif;
		font-weight: lighter;
	}
	h1 {
		color: #444;
	}
</style>

<script>
	var win = window.dialogArguments || opener || parent || top;
	win.window.scrollTo(0,0);
</script>

<?php
echo '<h1>BackupBuddy - Undo Changes</h1>';

$abspath = rtrim( dirname( __FILE__ ), '\\/' ) . '/';
define( 'ABSPATH', $abspath );


if ( ! isset( $_GET['confirm'] ) || ( '1' != $_GET['confirm'] ) ) { // Do rollback since user confirmed.
	echo 'Are you sure you want to undo these latest database changes? <a href="?confirm=1">Click here to confirm.</a>';
	echo '<br><br>';
	echo 'If you do not want to undo the changes you may safely delete this file, ' . basename( __FILE__ ) . '.';
	die();
}


// Determine database connection information and connect to DB.
$configFile = '';
if ( ! file_exists( ABSPATH . 'wp-config.php' ) ) { // Normal config file not found so warn or see if parent config may exist.
	$parentConfig =  dirname( ABSPATH ) . '/wp-config.php';
	if ( @file_exists( $parentConfig ) ) { // Parent config exists so offer it as an option or possibly use it if user has selected to do so.
		if ( pb_backupbuddy::_GET( 'parent_config' ) == 'true' ) { // User opted to use parent config.
			$configFile = $parentConfig;
		}
	}
	unset( $parentConfig );
} else { // Use normal config file.
	$configFile = ABSPATH . 'wp-config.php';
}
if ( '' == $configFile ) {
	die( 'Error #4534434: wp-config.php file not found.' );
}
// Read in wp-config.php file contents.
$configContents = file_get_contents( $configFile );
if ( false === $configContents ) {
	pb_backupbuddy::alert( 'Error: Unable to read wp-config.php configuration file.' );
	return;
}

// Grab database settings from wp-config.php contents.
$databaseSettings = array();
preg_match( '/define\([\s]*(\'|")DB_NAME(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $configContents, $matches );
$databaseSettings['name'] = $matches[4];
preg_match( '/define\([\s]*(\'|")DB_USER(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $configContents, $matches );
$databaseSettings['username'] = $matches[4];
preg_match( '/define\([\s]*(\'|")DB_PASSWORD(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $configContents, $matches );
$databaseSettings['password'] = $matches[4];
preg_match( '/define\([\s]*(\'|")DB_HOST(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $configContents, $matches );
$databaseSettings['host'] = $matches[4];
preg_match( '/\$table_prefix[\s]*=[\s]*(\'|")(.*)(\'|");/i', $configContents, $matches );
$databaseSettings['prefix'] = $matches[2];
// Connect to DB.
@mysql_connect( $databaseSettings['host'], $databaseSettings['username'], $databaseSettings['password'] ) or die( 'Error #45543434: Unable to connect to database based on wp-config.php settings.' );
@mysql_select_db( $databaseSettings['name'] ) or die( 'Error #5484584: Unable to select database based on wp-config.php settings.' );

$serial = str_replace( '.php', '', str_replace( 'backupbuddy_rollback_undo-', '', basename( __FILE__ ) ) );

echo '<h4>Rolling back changes...</h4>';



// Enable Maintenance Mode if not already.
if ( ! file_exists( ABSPATH . '.maintenance' ) ) {
	@file_put_contents( ABSPATH . '.maintenance', "<?php die( 'Site undergoing maintenance.' ); ?>" );
}




// Find tables matching temp OLD (original live tables) prefix.
$tempPrefix = 'BBold-' . substr( $serial, 0, 4 ) . '_';
$sql = "SELECT table_name FROM information_schema.tables WHERE table_name LIKE '" . str_replace( '_', '\_', $tempPrefix ) . "%' AND table_schema = DATABASE()";
echo 'Looking for tables with prefix `' . $tempPrefix . '` to rename.<br>';
if ( false === ( $tempTables = mysql_query( $sql ) ) ) {
	echo 'Error #89294: `' . mysql_error() . '` in SQL `' . $sql . '`.<br>';
	die();
}

// Loop through all BBold-SERIAL_ tables, renaming them back to live, deleting collisions as they occur.
while( $tempTable = mysql_fetch_row( $tempTables ) ) {
	$nonTempName = str_replace( $tempPrefix, '', $tempTable[0] );

	// CHECK if $nonTempName table exists in db. If it does then DROP the table.
	if ( false === ( $result = mysql_query( "SELECT table_name FROM information_schema.tables WHERE table_name LIKE '" . str_replace( '_', '\_', $nonTempName ) . "%' AND table_schema = DATABASE()" ) ) ) {
		echo 'Error #89294: `' . mysql_error() . '`.<br>';
	}
	if ( mysql_num_rows( $result ) > 0 ) { // WordPress EXISTS already. Collision.
		if ( false === mysql_query("DROP TABLE `" . mysql_real_escape_string( $nonTempName ) . "`") ) {
			echo 'Error #24873: `' . mysql_error() . '`.<br>';
		}
	}
	unset( $result );

	// RENAME $tempTable to $nonTempName
	$sql = "RENAME TABLE `" . mysql_real_escape_string( $tempTable[0] ) . "` TO `" . mysql_real_escape_string( $nonTempName ) . "`";
	echo $sql . '<br>';
	$result = mysql_query( $sql );
	if ( false === $result ) { // Failed.
		echo 'Error #54924: `' . mysql_error() . '`.<br>';
	}
}




// Drop any remaining temporary just-imported tables.
$tempPrefix = 'BBnew-'; //. substr( $serial, 0, 4 ) . '_';
$sql = "SELECT table_name FROM information_schema.tables WHERE table_name LIKE '" . str_replace( '_', '\_', $tempPrefix ) . "%' AND table_schema = DATABASE()";
echo 'Looking for tables with prefix `' . $tempPrefix . '` to delete.<br>';
if ( false === ( $tempTables = mysql_query( $sql ) ) ) {
	echo 'Error #89294: `' . mysql_error() . '` in SQL `' . $sql . '`.<br>';
	die();
}
// Loop through all BBnew-SERIAL_ tables, dropping.
while( $tempTable = mysql_fetch_row( $tempTables ) ) {
	if ( false === mysql_query("DROP TABLE `" . mysql_real_escape_string( $tempTable[0] ) . "`") ) {
		echo 'Error #24873: `' . mysql_error() . '`.<br>';
	}
}




// Turn OFF maintenance mode.
echo 'Disabling maintenance mode (if enabled).<br>';
if ( file_exists( ABSPATH . '.maintenance' ) ) {
	@unlink( ABSPATH . '.maintenance' );
}
echo '<h3>Database Changes Reversed</h3>';
echo 'The procedure was successfully cancelled & reversed. Your site should now function as before the changes were initiated.';




// Deleting any remaining importbuddy files.
echo 'Deleting any remaining importbuddy files.<br>';
function rrmdir($dir) {
	if (is_dir($dir)) {
	 $objects = scandir($dir);
	 foreach ($objects as $object) {
	   if ($object != "." && $object != "..") {
	     if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
	   }
	 }
	 reset($objects);
	 rmdir($dir);
	}
}
$importFiles = glob( dirname( __FILE__ ) . '/importbuddy*' );
foreach( $importFiles as $importFile ) {
	if ( is_dir( $importFile ) ) {
		rrmdir( @importFile );
	} else {
		unlink( @importFile );
	}
}




// Delete this script.
@unlink( __FILE__ );

if ( file_exists( __FILE__ ) ) {
	echo ' Unable to automatically delete this undo file. You may safely manually delete it.';
} else {
	echo ' This undo file has been deleted for you.';
}

?>
<script>
	var win = window.dialogArguments || opener || parent || top;
	win.pb_status_undourl( '' ); // Hide box.
</script>

