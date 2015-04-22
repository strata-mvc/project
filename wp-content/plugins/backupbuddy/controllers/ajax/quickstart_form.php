<?php
backupbuddy_core::verifyAjaxAccess();


pb_backupbuddy::verify_nonce();

// Quick Start form saving.

/* quickstart_form()
*
* Saving Quickstart form.
*
*/


$errors = array();
$form = pb_backupbuddy::_POST();
//print_r( $form );

if ( ( '' != $form['email'] ) && ( false !== stristr( $form['email'], '@' ) ) ) {
	pb_backupbuddy::$options['email_notify_error'] = strip_tags( $form['email'] );
} else {
	$errors[] = 'Invalid email address.';
}

if ( ( '' != $form['password'] ) && ( $form['password'] == $form['password_confirm'] ) ) {
	pb_backupbuddy::$options['importbuddy_pass_hash'] = md5( $form['password'] );
	pb_backupbuddy::$options['importbuddy_pass_length'] = strlen( $form['password'] );
} elseif ( '' == $form['password'] ) {
	$errors[] = 'Please enter a password for restoring / migrating.';
} else {
	$errors[] = 'Passwords do not match.';
}

if ( '' != $form['schedule'] ) {
	$destination_id = '';
	if ( '' != $form['destination_id'] ) { // Dest id explicitly set.
		$destination_id = $form['destination_id'];
	} else { // No explicit destination ID; deduce it.
		if ( '' != $form['destination'] ) {
			foreach( pb_backupbuddy::$options['remote_destinations'] as $destination_index => $destination ) { // Loop through ending with the last created destination of this type.
				if ( $destination['type'] == $form['destination'] ) {
					$destination_id = $destination_index;
				}
			}
		}
	}
	
	function pb_backupbuddy_schedule_exist_by_title( $title ) {
		foreach( pb_backupbuddy::$options['schedules'] as $schedule ) {
			if ( $schedule['title'] == $title ) {
				return true;
			}
		}
		return false;
	}
	
	// STARTER
	if ( 'starter' == $form['schedule'] ) {
		
		$title = 'Weekly Database (Quick Setup - Starter)';
		if ( false === pb_backupbuddy_schedule_exist_by_title( $title ) ) {
			$add_response = backupbuddy_api::addSchedule(
				$title,
				$profile = '1',
				$interval = 'weekly',
				$first_run = ( time() + ( get_option( 'gmt_offset' ) * 3600 ) + 86400 ),
				$remote_destinations = array( $destination_id )
			);
			if ( true !== $add_response ) { $errors[] = $add_response; }
		}
		
		$title = 'Monthly Full (Quick Setup - Starter)';
		if ( false === pb_backupbuddy_schedule_exist_by_title( $title ) ) {
			$add_response = backupbuddy_api::addSchedule(
				$title,
				$profile = '2',
				$interval = 'monthly',
				$first_run = ( time() + ( get_option( 'gmt_offset' ) * 3600 ) + 86400 + 18000 ),
				$remote_destinations = array( $destination_id )
			);
			if ( true !== $add_response ) { $errors[] = $add_response; }
		}
		
	}
	
	// BLOGGER
	if ( 'blogger' == $form['schedule'] ) {
		
		$title = 'Daily Database (Quick Setup - Blogger)';
		if ( false === pb_backupbuddy_schedule_exist_by_title( $title ) ) {
			$add_response = backupbuddy_api::addSchedule(
				$title,
				$profile = '1',
				$interval = 'daily',
				$first_run = ( time() + ( get_option( 'gmt_offset' ) * 3600 ) + 86400 ),
				$remote_destinations = array( $destination_id )
			);
			if ( true !== $add_response ) { $errors[] = $add_response; }
		}
		
		$title = 'Weekly Full (Quick Setup - Blogger)';
		if ( false === pb_backupbuddy_schedule_exist_by_title( $title ) ) {
			$add_response = backupbuddy_api::addSchedule(
				$title,
				$profile = '2',
				$interval = 'weekly',
				$first_run = ( time() + ( get_option( 'gmt_offset' ) * 3600 ) + 86400 + 18000 ),
				$remote_destinations = array( $destination_id )
			);
			if ( true !== $add_response ) { $errors[] = $add_response; }
		}
		
	}
	
	
} // end set schedule.


if ( 0 == count( $errors ) ) {
	pb_backupbuddy::save();
	die( 'Success.' );
} else {
	die( implode( "\n", $errors ) );
}

