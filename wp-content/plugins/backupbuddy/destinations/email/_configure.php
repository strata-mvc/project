<?php
/*
	Pre-populated variables coming into this script:
		$destination_settings
		$mode
*/

global $pb_hide_test, $pb_hide_save;
$pb_hide_test = false;

$default_name = NULL;
if ( 'add' == $mode ) {
	$default_name = 'My Email';
}
$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'title',
	'title'		=>		__( 'Destination name', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( 'Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-45]',
	'default'	=>		$default_name,
) );

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'address',
	'title'		=>		__( 'Email address', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: your@email.com] - Email address for this destination.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|email',
) );
