<?php
// Incoming variables: $backup from controllers/pages/_backup_home.php

if ( '1' == pb_backupbuddy::_GET( 'skip_quicksetup' ) ) {
	pb_backupbuddy::$options['skip_quicksetup'] = '1';
	pb_backupbuddy::save();
}


// Popup Quickstart modal if appears to be new install & quickstart not skip.
if (
	( pb_backupbuddy::_GET( 'wizard' ) == '1' )
	||
	(
		( '0' == pb_backupbuddy::$options['skip_quicksetup'] )
			&&
		( 0 == count( pb_backupbuddy::$options['schedules'] ) )
			&&
			( '' == pb_backupbuddy::$options['importbuddy_pass_hash'] )
		)
	)
  {
	pb_backupbuddy::$ui->title( 'BackupBuddy Quick Setup Wizard' );
	//echo "tb_show( 'BackupBuddy Quick Setup', '" . pb_backupbuddy::ajax_url( 'quickstart' ) . "&TB_iframe=1&width=640&height=455', null );";
	pb_backupbuddy::load_view( '_quicksetup', array() );
	return;
} else {
	pb_backupbuddy::$ui->title( __( 'Backup', 'it-l10n-backupbuddy' ) . ' <a href="javascript:void(0)" class="add-new-h2" onClick="jQuery(\'.backupbuddy-recent-backups\').toggle()">View recently made backups</a>' );
}



wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );


// Handle deleting profile.
if ( ( pb_backupbuddy::_GET( 'delete_profile' ) != '' ) && ( is_numeric( pb_backupbuddy::_GET( 'delete_profile' ) ) ) ) {
	if ( pb_backupbuddy::_GET( 'delete_profile' ) > 2 ) {
		if ( isset( pb_backupbuddy::$options['profiles'][pb_backupbuddy::_GET( 'delete_profile' )] ) ) {
			$profile_title = pb_backupbuddy::$options['profiles'][pb_backupbuddy::_GET( 'delete_profile' )]['title'];
			unset( pb_backupbuddy::$options['profiles'][pb_backupbuddy::_GET( 'delete_profile' )] );
			pb_backupbuddy::save();
			pb_backupbuddy::alert( 'Deleted profile "' . htmlentities( $profile_title ) . '".' );
		}
	} else {
		pb_backupbuddy::alert( 'Invalid profile ID. Cannot delete base profiles.' );
	}
}


// Add new profile.
if ( pb_backupbuddy::_POST( 'add_profile' ) == 'true' ) {
	pb_backupbuddy::verify_nonce();
	$error = false;
	if ( pb_backupbuddy::_POST( 'title' ) == '' ) {
		pb_backupbuddy::alert( 'Error: You must provide a new profile title.', true );
		$error = true;
	}
	if ( false === $error ) {
		$profile = array(
			'title'		=> htmlentities( pb_backupbuddy::_POST( 'title' ) ),
			'type'		=>	pb_backupbuddy::_POST( 'type' ),
		);
		$profile = array_merge( pb_backupbuddy::settings( 'profile_defaults' ), $profile );
		pb_backupbuddy::$options['profiles'][] = $profile;
		pb_backupbuddy::save();
		pb_backupbuddy::alert( 'New profile "' . htmlentities( pb_backupbuddy::_POST( 'title' ) ) . '" added. Select it from the list below to customize its settings and override global defaults.' );
	}
} // end if add profile.


?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		
		
		jQuery( '.profile_item_select' ).click( function() {
			var url = jQuery(this).attr( 'href' );
			url = url + '&after_destination=' + jQuery( '#pb_backupbuddy_backup_remotedestination' ).val();
			url = url + '&delete_after=' + jQuery( '#pb_backupbuddy_backup_deleteafter' ).val();
			window.location.href = url;
			return false;
		});
		
		
		// Click meta option in backup list to send a backup to a remote destination.
		jQuery( '.pb_backupbuddy_hoveraction_send' ).click( function(e) {
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'destination_picker' ); ?>&callback_data=' + jQuery(this).attr('rel') + '&sending=1&action_verb=to%20send%20to&TB_iframe=1&width=640&height=455', null );
			return false;
		});
		
		
		// Backup listing View Hash meta clicked.
		jQuery( '.pb_backupbuddy_hoveraction_hash' ).click( function(e) {
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'hash' ); ?>&callback_data=' + jQuery(this).attr('rel') + '&TB_iframe=1&width=640&height=455', null );
			return false;
		});
		
		
		// Click label for after backup remote send.
		jQuery( '#pb_backupbuddy_afterbackupremote' ).click( function(e) {
			var checkbox = jQuery( '#pb_backupbuddy_afterbackupremote_box' );
			checkbox.prop('checked', !checkbox[0].checked);
			
			if ( checkbox[0].checked ) { // Only show if just checked.
				afterbackupremote();
			}
			return false;
		});
		
		
		// Click checkbox for after backup remote send.
		jQuery( '#pb_backupbuddy_afterbackupremote_box' ).click( function(e) {
			var checkbox = jQuery( '#pb_backupbuddy_afterbackupremote_box' );
			if ( checkbox[0].checked ) { // Only show if just checked.
				afterbackupremote();
			}
		});
		
		
		// Click profile config gear next to a profile to pop up modal for editing its settings.
		jQuery( '.profile_settings' ).click( function(e) {
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'profile_settings' ); ?>&profile=' + jQuery(this).attr( 'rel' ) + '&callback_data=' + jQuery(this).attr('rel') + '&TB_iframe=1&width=640&height=455', null );
			return false;
		});
		
		
		// Clicked + sign to add a new profile.
		jQuery( '#pb_backupbuddy_profileadd_plusbutton' ).click( function() {
			jQuery(this).hide();
			jQuery( '#pb_backupbuddy_profileadd' ).slideDown();
			return false;
		});
		
		
		// Click the meta option in the backup list to apply a note to a backup.
		jQuery( '.pb_backupbuddy_hoveraction_note' ).click( function(e) {
			
			var existing_note = jQuery(this).parents( 'td' ).find('.pb_backupbuddy_notetext').text();
			if ( existing_note == '' ) {
				existing_note = 'My first backup';
			}
			
			var note_text = prompt( '<?php _e( 'Enter a short descriptive note to apply to this archive for your reference. (175 characters max)', 'it-l10n-backupbuddy' ); ?>', existing_note );
			if ( ( note_text == null ) || ( note_text == '' ) ) {
				// User cancelled.
			} else {
				jQuery( '.pb_backupbuddy_backuplist_loading' ).show();
				jQuery.post( '<?php echo pb_backupbuddy::ajax_url( 'set_backup_note' ); ?>', { backup_file: jQuery(this).attr('rel'), note: note_text }, 
					function(data) {
						data = jQuery.trim( data );
						jQuery( '.pb_backupbuddy_backuplist_loading' ).hide();
						if ( data != '1' ) {
							alert( "<?php _e('Error', 'it-l10n-backupbuddy' );?>: " + data );
						}
						javascript:location.reload(true);
					}
				);
			}
			return false;
		});
		
		
		
	}); // end jquery document ready.
	
	
	
	function pb_backupbuddy_profile_updated( profileID, profileTitle ) {
		jQuery( '#profile_title_' + profileID ).text( profileTitle );
	}
	
	
	function pb_backupbuddy_selectdestination( destination_id, destination_title, callback_data, delete_after, mode ) {
		
		if ( ( callback_data != '' ) && ( callback_data != 'delayed_send' ) ) {
			jQuery.post( '<?php echo pb_backupbuddy::ajax_url( 'remote_send' ); ?>', { destination_id: destination_id, destination_title: destination_title, file: callback_data, trigger: 'manual', delete_after: delete_after }, 
				function(data) {
					data = jQuery.trim( data );
					if ( data.charAt(0) != '1' ) {
						alert( "<?php _e("Error starting remote send", 'it-l10n-backupbuddy' ); ?>:" + "\n\n" + data );
					} else {
						if ( delete_after == true ) {
							var delete_alert = "<?php _e( 'The local backup will be deleted upon successful transfer as selected.', 'it-l10n-backupbuddy' ); ?>";
						} else {
							var delete_alert = '';
						}
						alert( "<?php _e('Your file has been scheduled to be sent now. It should arrive shortly.', 'it-l10n-backupbuddy' ); ?> <?php _e( 'You will be notified by email if any problems are encountered.', 'it-l10n-backupbuddy' ); ?>" + " " + delete_alert + "\n\n" + data.slice(1) );
						/* Try to ping server to nudge cron along since sometimes it doesnt trigger as expected. */
						jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>',
							function(data) {
							}
						);
					}
				}
			);
		} else if ( callback_data == 'delayed_send' ) { // Specified a destination to send to later.
			jQuery( '#pb_backupbuddy_backup_remotedestination' ).val( destination_id );
			jQuery( '#pb_backupbuddy_backup_deleteafter' ).val( delete_after );
			jQuery( '#pb_backupbuddy_backup_remotetitle' ).html( 'Destination: "' + destination_title + '".' );
			jQuery( '#pb_backupbuddy_backup_remotetitle' ).slideDown();
		} else {
			window.location.href = '<?php
			if ( is_network_admin() ) {
				echo network_admin_url( 'admin.php' );
			} else {
				echo admin_url( 'admin.php' );
			}
			?>?page=pb_backupbuddy_backup&custom=remoteclient&destination_id=' + destination_id;
		}
	} // end pb_backupbuddy_selectdestination().
	
	
	function afterbackupremote() {
		tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'destination_picker' ); ?>&callback_data=delayed_send&sending=1&action_verb=to%20send%20to&TB_iframe=1&width=640&height=455', null );
	} // end afterbackupremote().
	
</script>



<style> 
	.profile_box {
		background: #F8F8F8;
		margin: 0;
		display: block;
		border-radius: 5px;
		padding: 10px 10px 0px 10px;
		margin-bottom: 40px;
		border-radius: 5px;
		border: 1px solid #d6d6d6;
		border-top: 1px solid #ebebeb;
		box-shadow: 0px 3px 0px 0px #aaaaaa;
		box-shadow: 0px 3px 0px 0px #CFCFCF;
		font-size: auto;
		//min-height: 65px;
	}
	.profile_text {
		display: block;
		float: left;
		line-height: 26px;
		//margin-right: 8px;
		font-weight: bold;
		padding-right: 8px;
	}
	.profile_type {
		display: block;
		float: left;
		line-height: 26px;
		margin-right: 10px;
		//width: 68px;
		color: #aaa;
		
		padding-right: 10px;
		border-right: 1px solid #EBEBEB;
	}
	
	.profile_item_select,.profile_item_noselect {
		display: block;
		background: #fff;
		border: 1px solid #e7e7e7;
		border-top: 1px solid #ebebeb;
		border-bottom: 1px solid #c9c9c9;
		border-radius: 4px 0 0 4px;
		//padding: 15px 20px 15px 15px;
		padding: 15px 1%;
		margin-bottom: 10px;
		text-decoration: none;
		color: #252525;
		float: left;
		//width: 90%;
		line-height: 2;
		font-size: medium;
	}
	.bb-dest-option .info.add-new {
		width: 95%;
		padding-right: 3%;
		border-radius: 4px;
	}
	
	.profile_item_select:hover,.profile_item_noselect:hover {
		color: #da2828;
	}
	.profile_item_select:active, .profile_item_select:focus,.profile_item_noselect:active, .profile_item_noselect:focus {
		box-shadow: inset 0 0 5px #da2828;
	}
	
	.profile_item {
		margin-right: 15px;
	}
	.profile_item:hover {
		color: #da2828;
		cursor: pointer;
	}
	
	.profile_item_add_select {
		border-radius: 4px 4px 4px 4px;
		padding: 12px;
	}

	.profile_item_selected {
		border-bottom: 3px solid #da2828;
		margin-bottom: 10px;
	}

	.profile_choose {
		font-size: 20px;
		font-family: "HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",sans-serif;
		padding: 5px 0 15px 5px;
		color: #464646;
	}
	.backupbuddyFileTitle {
		//color: #0084CB;
		color: #000;
		font-size: 1.2em;
	}
	
	.profile_settings {
		display: block;
		float: left;
		height: 34px;
		/*
		width: 20px;
		padding: 11px;
		*/
		padding: 11px 1%;
		width: 20px;
		margin-top: 0;
		margin-right: 12px;
		margin-bottom: 10px;
		background-size: 20px 20px;
		border-radius: 0 4px 4px 0;
		border-right: 1px solid #e7e7e7;
		border-top: 1px solid #ebebeb;
		border-bottom: 1px solid #c9c9c9;
		
		background-position: center;
		background-repeat:no-repeat;
		background-color: #fff;	
		background-size: 20px 20px;
	}
	.profile_settings:hover {
		background-color: #a8a8a8;
		background-size: 20px 20px;
		box-shadow: inset 0 0 8px #666;
	}
	.profile_add {
		display: block;
		width: 32px;
		height: 32px;
		background: transparent url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/dest_plus.png') top left no-repeat;
		vertical-align: -3px;
	}
	.profile_add:hover {
		background: transparent url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/dest_plus.png') bottom left no-repeat;
	}
</style>



<br>
<div class="backupbuddy-recent-backups" style="display: none;">
	
	<?php
	$recentBackups_list = glob( backupbuddy_core::getLogDirectory() . 'fileoptions/*.txt' );
	if ( ! is_array( $recentBackups_list ) ) {
		$recentBackups_list = array();
	}
	
	if ( count( $recentBackups_list ) == 0 ) {
		_e( 'No backups have been created recently.', 'it-l10n-backupbuddy' );
	} else {
		
		// Backup type.
		$pretty_type = array(
			'full'	=>	'Full',
			'db'	=>	'Database',
			'files' =>	'Files',
		);
		
		// Read in list of backups.
		$recent_backup_count_cap = 5; // Max number of recent backups to list.
		$recentBackups = array();
		foreach( $recentBackups_list as $backup_fileoptions ) {
			
			require_once( pb_backupbuddy::plugin_path() . '/classes/fileoptions.php' );
			pb_backupbuddy::status( 'details', 'Fileoptions instance #1.' );
			$backup = new pb_backupbuddy_fileoptions( $backup_fileoptions, $read_only = true );
			if ( true !== ( $result = $backup->is_ok() ) ) {
				pb_backupbuddy::status( 'error', __('Unable to access fileoptions data file.', 'it-l10n-backupbuddy' ) . ' Error: ' . $result );
				continue;
			}
			$backup = &$backup->options;
			
			if ( !isset( $backup['serial'] ) || ( $backup['serial'] == '' ) ) {
				continue;
			}
			if ( ( $backup['finish_time'] >= $backup['start_time'] ) && ( 0 != $backup['start_time'] ) ) {
				$status = '<span class="pb_label pb_label-success">Completed</span>';
			} elseif ( $backup['finish_time'] == -1 ) {
				$status = '<span class="pb_label pb_label-warning">Cancelled</span>';
			} elseif ( FALSE === $backup['finish_time'] ) {
				$status = '<span class="pb_label pb_label-error">Failed (timeout?)</span>';
			} elseif ( ( time() - $backup['updated_time'] ) > backupbuddy_constants::TIME_BEFORE_CONSIDERED_TIMEOUT ) {
				$status = '<span class="pb_label pb_label-error">Failed (likely timeout)</span>';
			} else {
				$status = '<span class="pb_label pb_label-warning">In progress or timed out</span>';
			}
			$status .= '<br>';
			
			
			// Technical details link.
			$status .= '<div class="row-actions">';
			$status .= '<a title="' . __( 'Backup Process Technical Details', 'it-l10n-backupbuddy' ) . '" href="' . pb_backupbuddy::ajax_url( 'integrity_status' ) . '&serial=' . $backup['serial'] . '&#038;TB_iframe=1&#038;width=640&#038;height=600" class="thickbox">View Details</a>';
			
			$sumLogFile = backupbuddy_core::getLogDirectory() . 'status-' . $backup['serial'] . '_sum_' . pb_backupbuddy::$options['log_serial'] . '.txt';
			if ( file_exists( $sumLogFile ) ) {
				$status .= '<div class="row-actions"><a title="' . __( 'View Backup Log', 'it-l10n-backupbuddy' ) . '" href="' . pb_backupbuddy::ajax_url( 'view_log' ) . '&serial=' . $backup['serial'] . '&#038;TB_iframe=1&#038;width=640&#038;height=600" class="thickbox">' . __( 'View Log', 'it-l10n-backupbuddy' ) . '</a></div>';
			}
			
			$status .= '</div>';
			
			// Calculate finish time (if finished).
			if ( $backup['finish_time'] > 0 ) {
				$finish_time = pb_backupbuddy::$format->date( pb_backupbuddy::$format->localize_time( $backup['finish_time'] ) ) . '<br><span class="description">' . pb_backupbuddy::$format->time_ago( $backup['finish_time'] ) . ' ago</span>';
			} else { // unfinished.
				$finish_time = '<i>Unfinished</i>';
			}
			
			$backupTitle = '<span class="backupbuddyFileTitle" style="color: #000;" title="' . basename( $backup['archive_file'] ) . '">' . pb_backupbuddy::$format->date( pb_backupbuddy::$format->localize_time( $backup['start_time'] ), 'l, F j, Y - g:i:s a' ) . ' (' . pb_backupbuddy::$format->time_ago( $backup['start_time'] ) . ' ago)</span><br><span class="description">' . basename( $backup['archive_file'] ) . '</span>';
			
			if ( isset( $backup['profile'] ) ) {
				$backupType = '<div>
					<span style="color: #AAA; float: left;">' . pb_backupbuddy::$format->prettify( $backup['profile']['type'], $pretty_type ) . '</span>
					<span style="display: inline-block; float: left; height: 15px; border-right: 1px solid #EBEBEB; margin-left: 6px; margin-right: 6px;"></span>'
					. $backup['profile']['title'] .
				'</div>';
			} else {
				$backupType = '<span class="description">Unknown</span>';
			}
			
			if ( isset( $backup['archive_size'] ) && ( $backup['archive_size'] > 0 ) ) {
				$archive_size = pb_backupbuddy::$format->file_size( $backup['archive_size'] );
			} else {
				$archive_size = 'n/a';
			}
			
			// Append to list.
			$recentBackups[ $backup['serial'] ] = array(
				array( basename( $backup['archive_file'] ), $backupTitle ),
				$backupType,
				$archive_size,
				ucfirst( $backup['trigger'] ),
				$status,
				'start_timestamp' => $backup['start_time'], // Used by array sorter later to put backups in proper order.
			);
			
		}

		$columns = array(
			__('Recently Made Backups (Start Time)', 'it-l10n-backupbuddy' ),
			__('Type | Profile', 'it-l10n-backupbuddy' ),
			__('File Size', 'it-l10n-backupbuddy' ),
			__('Trigger', 'it-l10n-backupbuddy' ),
			__('Status', 'it-l10n-backupbuddy' ),
		);

		function pb_backupbuddy_aasort (&$array, $key) {
			$sorter=array();
			$ret=array();
			reset($array);
			foreach ($array as $ii => $va) {
			    $sorter[$ii]=$va[$key];
			}
			asort($sorter);
			foreach ($sorter as $ii => $va) {
			    $ret[$ii]=$array[$ii];
			}
			$array=$ret;
		}

		pb_backupbuddy_aasort( $recentBackups, 'start_timestamp' ); // Sort by multidimensional array with key start_timestamp.
		$recentBackups = array_reverse( $recentBackups ); // Reverse array order to show newest first.
		
		$recentBackups = array_slice( $recentBackups, 0, $recent_backup_count_cap ); // Only display most recent X number of backups in list.
		
		pb_backupbuddy::$ui->list_table(
			$recentBackups,
			array(
				'action'		=>	pb_backupbuddy::page_url(),
				'columns'		=>	$columns,
				'css'			=>	'width: 100%;',
			)
		);
		
		echo '<div class="alignright actions">';
		pb_backupbuddy::$ui->note( 'Hover over items above for additional options.' );
		echo '</div>';
		
	} // end if recent backups exist.
	?>
	
	<br><br><br>
</div>




<div class="profile_box">
	<div class="profile_choose">
		<?php _e( 'Choose a backup profile to run:', 'it-l10n-backupbuddy' ); ?>
	</div>
	
	<?php
	if ( true === $disableBackingUp ) {
		echo '&nbsp;&nbsp;<span class="description">' . __( 'Backing up disabled due to errors listed above. This often caused by permission problems on files/directories. Please correct the errors above and refresh to try again.', 'it-l10n-backupbuddy' ) . '</span><br>';
	} else {
		foreach( pb_backupbuddy::$options['profiles'] as $profile_id => $profile ) {
			if ( $profile['type'] == 'defaults' ) { continue; } // Skip showing defaults here...
			?>
			<div class="profile_item">
				<a class="profile_item_select" href="<?php echo pb_backupbuddy::page_url(); ?>&backupbuddy_backup=<?php echo $profile_id; ?>" title="Create this <?php echo $profile['type']; ?> backup.">
					<span class="profile_type"><?php
						if ( $profile['type'] == 'db' ) {
							_e( 'Database', 'it-l10n-backupbuddy' );
						} elseif ( $profile['type'] == 'full' ) {
							_e( 'Full', 'it-l10n-backupbuddy' );
						} elseif( $profile['type'] == 'files' ) {
							_e( 'Files', 'it-l10n-backupbuddy' );
						} else {
							echo 'unknown(' . htmlentities( $profile['type'] ). ')';
						}
					?></span>
					<span class="profile_text" id="profile_title_<?php echo $profile_id; ?>"><?php echo htmlentities( $profile['title'] ); ?></span>
				</a>
				<a href="#settings" rel="<?php echo $profile_id; ?>" class="profile_settings" style="background-image: url('<?php echo pb_backupbuddy::plugin_url(); ?>/images/dest_gear.png');" title="<?php _e( "Configure this profile's settings.", 'it-l10n-backupbuddy' ); ?>"></a>
			</div>
			<?php
		}
		?>
		
		<div class="profile_item" id="pb_backupbuddy_profileadd_plusbutton">
			<a class="profile_item_noselect profile_item_add_select" title="<?php _e( 'Create new profile.', 'it-l10n-backupbuddy' ); ?>">
				<span class="profile_add"></span>
			</a>
		</div>
		
		<div class="profile_item" id="pb_backupbuddy_profileadd" style="display: none;" href="<?php echo pb_backupbuddy::ajax_url( 'backup_profile_settings' ); ?>&profile=<?php echo $profile_id; ?>">
			<div class="profile_item_noselect" style="padding: 11px;">
				<form method="post" action="?page=pb_backupbuddy_backup" style="white-space:nowrap;">
					<?php pb_backupbuddy::nonce(); ?>
					<input type="hidden" name="add_profile" value="true">
					<span class="profile_type">
						<select name="type">
							<option value="db"><?php _e( 'Database only', 'it-l10n-backupbuddy' ); ?></option>
							<option value="full"><?php _e( 'Full (DB + Files)', 'it-l10n-backupbuddy' ); ?></option>
							<option value="files"><?php _e( 'Files only (BETA)', 'it-l10n-backupbuddy' ); ?></option>
						</select>
					</span>
					<span class="profile_text"><input type="text" name="title" style="width: 150px" maxlength="20" placeholder="<?php _e( 'New profile title...', 'it-l10n-backupbuddy' ); ?>"></span>
					<input type="submit" name="submit" value="+ <?php _e( 'Add', 'it-l10n-backupbuddy' ); ?>" class="button button-primary" style="vertical-align: 3px; margin-left: 3px;">
				</form>
			</div>
		</div>
		
		<br style="clear: both;">
		
		<!-- Remote send after successful backup? -->
		<div style="clear: both; padding-left: 4px;">
			<input type="checkbox" name="pb_backupbuddy_afterbackupremote" id="pb_backupbuddy_afterbackupremote_box"> <label id="pb_backupbuddy_afterbackupremote" for="pb_backupbuddy_afterbackupremote">Send to remote destination as part of backup process. <span id="pb_backupbuddy_backup_remotetitle"></span></label>
			
			<input type="hidden" name="remote_destination" id="pb_backupbuddy_backup_remotedestination">
			<input type="hidden" name="delete_after" id="pb_backupbuddy_backup_deleteafter">
			
		</div>
	<?php } // end disabling backups ?>
	<br style="clear: both;">
	
</div>





<?php
pb_backupbuddy::flush();

$listing_mode = 'default';
require_once( '_backup_listing.php' );

/*
echo '<br><br>';
echo '<a href="';
if ( is_network_admin() ) {
	echo network_admin_url( 'admin.php' );
} else {
	echo admin_url( 'admin.php' );
}
echo '?page=pb_backupbuddy_destinations" class="button button-primary">View & Manage remote destination files</a>';
*/



echo '<br style="clear: both;"><br><br><br>';

// Handles thickbox auto-resizing. Keep at bottom of page to avoid issues.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}

