<?php

class pb_backupbuddy_filters extends pb_backupbuddy_filterscore {
	
	
	
	/* cron_scheduled()
	 *
	 * Adds in additional scheduling intervals into WordPress such as weekly, twice monthly, monthly, etc.
	 *
	 * @param	$schedules	array	Array of existing schedule intervals already registered with WordPress. Handles missing param or not being an array.
	 * @return				array	Array containing old and new schedule intervals.
	 */
	public function cron_schedules( $schedules = array() ) {
		if ( ! is_array( $schedules ) ) {
			$schedules = array();
		}
		
		$schedules['twicedaily'] = array( 'interval' => 43200, 'display' => 'Twice Daily' );
		$schedules['everyotherday'] = array( 'interval' => 172800, 'display' => 'Every Other Day' );
		$schedules['twiceweekly'] = array( 'interval' => 302400, 'display' => 'Twice Weekly' );
		$schedules['weekly'] = array( 'interval' => 604800, 'display' => 'Once Weekly' );
		$schedules['twicemonthly'] = array( 'interval' => 1296000, 'display' => 'Twice Monthly' );
		$schedules['monthly'] = array( 'interval' => 2592000, 'display' => 'Once Monthly' );
		$schedules['yearly'] = array( 'interval' => 31556900, 'display' => 'Once Yearly' );
		return $schedules;
	} // End cron_schedules().
	
	
	
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( isset( $plugin_meta[2] ) && strstr( $plugin_meta[2], 'backupbuddy' ) ) {
			$plugin_meta[] = '<a href="http://ithemes.com/codex/page/BackupBuddy" target="_blank">' . __( 'Documentation', 'it-l10n-backupbuddy' ) . '</a>';
			$plugin_meta[] = '<a href="http://ithemes.com/forum/" target="_blank">' . __( 'Support', 'it-l10n-backupbuddy' ) . '</a>';
			
			return $plugin_meta;
		} else {
			return $plugin_meta;
		}
	} // End plugin_row_meta().
	
	
	
} // End class pb_backupbuddy_filters.
?>