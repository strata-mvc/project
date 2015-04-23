<?php
if ( true !== $confirm ) {
	return false;
}

$next_scheduled_time = wp_next_scheduled( 'pb_backupbuddy-cron_scheduled_backup', array( (int)$scheduleID ) );
if ( FALSE === backupbuddy_core::unschedule_event( $next_scheduled_time, 'pb_backupbuddy-cron_scheduled_backup', array( (int)$scheduleID ) ) ) {
	return false;
}

unset( pb_backupbuddy::$options['schedules'][$scheduleID] );
pb_backupbuddy::save();

return true;