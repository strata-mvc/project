<?php
// Incoming vars:
// $deployDirection		push or pull
?>
<table class="widefat">
	<thead>
		<tr class="thead">
			<th>&nbsp;</th><th><?php echo $headFoot[0]; ?></th><th><span class="dashicons dashicons-arrow-right-alt"></span></th><th><?php echo $headFoot[1]; ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr class="thead">
			<th>&nbsp;</th><th><?php echo $headFoot[0]; ?></th><th><span class="dashicons dashicons-arrow-right-alt"></span></th><th><?php echo $headFoot[1]; ?></th>
		</tr>
	</tfoot>
	<tbody>
		<?php
		$i = 0;
		foreach( $pushRows as $pushTitle => $pushRow ) { ?>
			<tr class="entry-row alternate">
				<td class="tdhead"><?php echo $pushTitle; ?></td>
				<td><?php echo $pushRow[0]; ?></td>
				<td>&nbsp;</td>
				<td><?php echo $pushRow[1]?></td>
			</tr>
		<?php }
		?>
	</tbody>
</table>


<?php
if ( is_network_admin() ) {
	$backup_url = network_admin_url( 'admin.php' );
} else {
	$backup_url = admin_url( 'admin.php' );
}
$backup_url .= '?page=pb_backupbuddy_backup';
?>
<br>

<!-- <form id="pb_backupbuddy_deploy_form" method="post" action="<?php echo pb_backupbuddy::ajax_url( 'deploy' ); ?>?action=pb_backupbuddy_backupbuddy&function=deploy&step=run"> -->
<form target="_top" id="pb_backupbuddy_deploy_form" method="post" action="<?php echo $backup_url; ?>&backupbuddy_backup=deploy&direction=<?php echo $deployDirection; ?>">
	<input type="hidden" name="destination_id" value="<?php echo $destination_id; ?>">
	<h3>Database Find & Replace</h3>
	The site URL (www and domain) and paths will be updated. Serialized data will be accounted for.<br>
	<input type="text" value="<?php echo $localInfo['siteurl']; ?>" disabled> &rarr; <input type="text" value="<?php echo $deployData['remoteInfo']['siteurl']; ?>" disabled><br>
	<input type="text" value="<?php echo $localInfo['abspath']; ?>" disabled> &rarr; <input type="text" value="<?php echo $deployData['remoteInfo']['abspath']; ?>" disabled><br>
	<!-- <input type="text"> -&gt; <input type="text"> - +<br> -->
	<br>

	<style>
		.database_contents_select {
			padding: 5px;
			line-height: 1.7em;
			max-height: 100px;
			overflow: scroll;
			border: 1px solid #ddd;
			background: #f9f9f9;
			max-width: 400px;
		}
		.database_contents_select::-webkit-scrollbar {
			-webkit-appearance: none;
			width: 11px;
			height: 11px;
		}
		.database_contents_select::-webkit-scrollbar-thumb {
			border-radius: 8px;
			border: 2px solid white; /* should match background, can't be transparent */
			background-color: rgba(0, 0, 0, .1);
		}
		.database_contents_shortcuts {
			color: #ADADAD;
			margin-bottom: 3px;
		}
		.database_contents_shortcuts a {
			text-decoration: none;
			cursor: pointer;
		}
	</style>
	
	<script>
		jQuery(document).ready(function() {
			
			jQuery( '.database_contents_shortcuts-all' ).click( function(e){
				e.preventDefault();
				jQuery( '.database_contents_select' ).find( 'input' ).prop( 'checked', true );
			});
			
			jQuery( '.database_contents_shortcuts-none' ).click( function(e){
				e.preventDefault();
				jQuery( '.database_contents_select' ).find( 'input' ).prop( 'checked', false );
			});
			
			jQuery( '.database_contents_shortcuts-prefix' ).click( function(e){
				e.preventDefault();
				
				if ( 'push' == jQuery('#backupbuddy_deploy_direction').attr( 'data-direction' ) ) {
					prefix = jQuery( '#backupbuddy_deploy_prefixA' ).attr( 'data-prefix' );
				} else {
					prefix = jQuery( '#backupbuddy_deploy_prefixB' ).attr( 'data-prefix' );
				}
				
				jQuery( '.database_contents_select' ).find( 'input' ).each( function(index){
					if ( jQuery(this).val().indexOf( prefix ) == 0 ) {
						jQuery(this).prop( 'checked', true );
					} else {
						jQuery(this).prop( 'checked', false );
					}
				});
			});
			
		});
	</script>

	<h3><?php
		if ( 'pull' == $deployDirection ) {
			_e( 'Pull', 'it-l10n-backupbuddy' );
		} else { // push
			_e( 'Push', 'it-l10n-backupbuddy' );
		}
		echo ' ';
		_e( 'Database Contents', 'it-l10n-backupbuddy' );
	?></h3>
	<input type="hidden" name="backup_profile" value="1">
	<div class="database_contents_shortcuts">
		<a class="database_contents_shortcuts-all" title="Select all database tables.">Select All</a> | <a class="database_contents_shortcuts-none" title="Unselect all database tables.">Unselect All</a> | <a class="database_contents_shortcuts-prefix" title="Select database tables matching the WordPress table prefix of the source site.">WordPress Table Prefix</a>
	</div>
	<div class="database_contents_select">
		<?php
		if ( 'pull' == $deployDirection ) {
			$tables = $deployData['remoteInfo']['tables'];
		} else { // push
			$tables = $localInfo['tables'];
		}
		foreach( $tables as $table ) {
			echo '<label><input type="checkbox" name="tables[]" value="' . $table . '"> ' . $table . '</label><br>';
		}
		?>
	</div>
	<br>
	
	<h3><?php 
		if ( 'pull' == $deployDirection ) {
			_e( 'Pull', 'it-l10n-backupbuddy' );
		} else { // push
			_e( 'Push', 'it-l10n-backupbuddy' );
		}
		echo ' ';
		_e( 'Plugins', 'LIONS' );
	?></h3>
	<label><input type="checkbox" name="sendPlugins" value="true"> Update destination plugins with newer or missing versions to match.</label>
	<br><br>
	
	<h3><?php
		if ( 'pull' == $deployDirection ) {
			_e( 'Pull', 'it-l10n-backupbuddy' );
		} else { // push
			_e( 'Push', 'it-l10n-backupbuddy' );
		}
		echo ' ';
		_e( 'Active Theme', 'LIONS' ); ?></h3>
	<?php
	if ( $deployData['remoteInfo']['activeTheme'] == $localInfo['activeTheme'] ) {
		echo '<label><input type="checkbox" name="sendTheme" value="true"> Update destination active theme files with newer or missing files to match.</label>';
	} else {
		echo '<span class="description">' . __( 'Active theme differs so theme deployment is disabled.', 'it-l10n-backupbuddy' ) . '</span>';
	}
	?>
	<br><br>
	
	<h3><?php _e( 'Source Media / Atachments', 'LIONS' ); ?></h3>
	<label><input type="checkbox" name="sendMedia" value="true"> Update destination media files with newer or missing files to match.</label>
	<br><br>
	
	<br>
	<?php pb_backupbuddy::nonce(); ?>
	<input type="hidden" name="destination" value="<?php echo $destination_id; ?>">
	<input type="hidden" name="deployData" value="<?php echo base64_encode( serialize( $deployData ) ); ?>">
	<input type="submit" name="submitForm" class="button button-primary" value="<?php
	if ( 'pull' == $deployDirection ) {
		_e('Begin Pull (BETA)');
	} elseif( 'push' == $deployDirection ) {
		_e('Begin Push (BETA)');
	} else {
		echo '{Err3849374:UnknownDirection}';
	}
	?> &raquo;">
	
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	
	<a class="button button-secondary" onclick="jQuery('.pb_backupbuddy_advanced').toggle();">Advanced Options</a>
	<span class="pb_backupbuddy_advanced" style="display: none; margin-left: 15px;">
		<label>Source chunk time limit: <input size="5" maxlength="5" type="text" name="sourceMaxExecutionTime" value="<?php echo $localInfo['php']['max_execution_time']; ?>"> sec</label>
		&nbsp;&nbsp;&nbsp;
		<label>Destination chunk time limit: <input size="5" maxlength="5" type="text" name="destinationMaxExecutionTime" value="<?php echo $deployData['remoteInfo']['php']['max_execution_time']; ?>"> sec</label>
	</span>
	
</form>

