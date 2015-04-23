<?php
backupbuddy_core::verifyAjaxAccess();


pb_backupbuddy::$ui->ajax_header();

$serial = pb_backupbuddy::_GET( 'serial' );
$logFile = backupbuddy_core::getLogDirectory() . 'status-' . $serial . '_sum_' . pb_backupbuddy::$options['log_serial'] . '.txt';

if ( ! file_exists( $logFile ) ) {
	die( 'Error #858733: Log file `' . $logFile . '` not found or access denied.' );
}

$lines = file_get_contents( $logFile );
$lines = explode( "\n", $lines );
?>
Showing advanced format log file details. By default only errors are logged. Full logging mode will capture all backup details. Log file: <?php echo $logFile; ?><br><br>
<textarea readonly="readonly" id="backupbuddy_messages" wrap="off" style="width: 100%; min-height: 400px; height: 500px; height: 80%; background: #FFF;"><?php
foreach( (array)$lines as $line ) {
	$line = json_decode( $line, true );
	//print_r( $line );
	if ( is_array( $line ) ) {
		$u = '';
		if ( isset( $line['u'] ) ) { // As off v4.2.15.6. TODO: Remove this in a couple of versions once old logs without this will have cycled out.
			$u = '.' . $line['u'];
		}
		echo pb_backupbuddy::$format->date( $line['time'], 'G:i:s' ) . $u . "\t\t";
		echo $line['run'] . "sec\t";
		echo $line['mem'] . "MB\t";
		echo $line['event'] . "\t";
		echo $line['data'] . "\n";
	}
}
?></textarea>


<?php
pb_backupbuddy::$ui->ajax_footer();
die();