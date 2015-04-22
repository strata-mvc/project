<?php


// This file is automatically loaded for the getting started page as a `template` of sorts.
// The individual plugin getting started page is included from this.


// Set up supporting scripts and styles.
pb_backupbuddy::load_script( 'dashboard' );
pb_backupbuddy::load_style( 'dashboard' );
pb_backupbuddy::load_script( 'jquery-ui-tabs' );


//echo '<div style="float: right; width: 20%; margin-right: 30px; margin-left: 10px; margin-top: 60px;"><br><br>';
echo '<table width="100%"><tr><td valign="top" style="width: 80%;">';


if ( pb_backupbuddy::settings( 'series' ) != '' ) { // SERIES
	pb_backupbuddy::$ui->title( 'Getting Started with ' . pb_backupbuddy::settings( 'series' ) );
	?>
	<div id="pluginbuddy-tabs" style="width: 70%;">
		<ul>
			<?php
			global $pluginbuddy_series;
			
			$i = 0;
			foreach( $pluginbuddy_series[ pb_backupbuddy::settings( 'series' ) ] as $slug => $data ) {
				$i++;
				echo '<li type="disc"><a href="#pluginbuddy-tabs-' . $i . '"><span>' . $data['name'] . '</span></a></li>';
			}
			?>
		</ul>
		<div class="tabs-borderwrap">
			<?php
			$i = 0;
			foreach( $pluginbuddy_series[ pb_backupbuddy::settings( 'series' ) ] as $slug => $data ) {
				$i++;
				echo '<div id="pluginbuddy-tabs-' . $i . '">';
				
				if ( file_exists( $data['path'] . '/views/getting_started.php' ) ) {
					pb_backupbuddy::load_view( 'getting_started' );
				} else {
					echo '{views/getting_started.php not found.}';
				}
				
				echo '</div>';
				
				plugin_information( $slug, $data );
			}
			?>
		</div>
	</div>
	<?php
} else { // STANDALONE
	pb_backupbuddy::$ui->title( 'Getting Started with ' . pb_backupbuddy::settings( 'name' ) . ' v' . pb_backupbuddy::settings( 'version' ) );
	
	if ( file_exists( pb_backupbuddy::plugin_path() . '/views/getting_started.php' ) ) {
		pb_backupbuddy::load_view( 'getting_started', array( 'plugin_slug' => pb_backupbuddy::settings( 'slug' ) ) );
	} else {
		echo '{views/getting_started.php not found.}';
	}
	
	//plugin_information( pb_backupbuddy::settings( 'slug' ), array( 'name' => pb_backupbuddy::settings( 'name' ), 'path' => pb_backupbuddy::plugin_path() ) );
}



echo '</td><td>&nbsp;&nbsp;</td><td valign="top" style="padding-top: 40px;">';



pb_backupbuddy::$ui->start_metabox( 'Tutorials & Support', true, true );
?>
- <a href="http://ithemes.com/publishing/getting-started-with-backupbuddy/" target="_blank" style="text-decoration: none;">Getting Started eBook</a><br>
- <a href="http://ithemes.com/backupbuddy-training/" target="_blank" style="text-decoration: none;">Tutorial & Walkthrough Videos</a><br>
- <a href="http://ithemes.tv/category/backupbuddy/" target="_blank" style="text-decoration: none;">Getting Started Videos</a><br>
- <a href="http://ithemes.com/codex/" target="_blank" style="text-decoration: none;">Knowledge Base Codex</a><br>
- <a href="http://ithemes.com/support/" target="_blank" style="text-decoration: none;">Support Forum</a>
<?php
pb_backupbuddy::$ui->end_metabox();

pb_backupbuddy::$ui->start_metabox( 'iThemes', true, true );

echo '<p style="font-weight: bold; left: -10px;"><a href="http://ithemes.com/" style="text-decoration: none;"><img src="' . pb_backupbuddy::plugin_url() . '/images/pluginbuddy.png" style="vertical-align: -3px;"> Things to do . . .</a></p>';
echo '<ol class="pluginbuddy-nodecor" style="margin-left: 10px;">';
echo '	<li style="list-style-type: none;"><a href="http://twitter.com/home?status=' . urlencode('Check out this awesome plugin, ' . pb_backupbuddy::settings( 'name' ) . '! http://getbackupbuddy.com @backup_buddy') . '" title="Share on Twitter" onClick="window.open(jQuery(this).attr(\'href\'),\'ithemes_popup\',\'toolbar=0,status=0,width=820,height=500,scrollbars=1\'); return false;">Tweet about this plugin</a></li>';
echo '	<li style="list-style-type: none;"><a href="http://ithemes.com/find/plugins/" target="_blank">Plugins by iThemes</a></li>';
echo '	<li style="list-style-type: none;"><a href="http://ithemes.com/find/themes/" target="_blank">Themes by iThemes</a></li>';
echo '	<li style="list-style-type: none;"><a href="http://pluginbuddy.com/subscribe/" style="text-decoration: none;">Subscribe to Email Newsletter</a></li>';
echo '</ol>';

echo '<p style="font-weight: bold; left: -10px;"><a href="http://twitter.com/ithemes/" style="text-decoration: none;"><img src="' . pb_backupbuddy::plugin_url() . '/pluginbuddy/images/pluginbuddy.png" style="vertical-align: -3px;"> iThemes.com News</a></p>';
echo pb_backupbuddy::$ui->get_feed( 'http://ithemes.com/feed/', 5 );

pb_backupbuddy::$ui->end_metabox();



echo '</td></tr></table>';


function plugin_information( $plugin_slug, $data ) {
	$plugin_path = $data['path'];
	?>
	
	<?php pb_backupbuddy::$ui->start_metabox( 'Version History', true, 'width: 100%;' ); ?>
		<textarea readonly="readonly" rows="7" cols="65" wrap="off" style="width: 100%;"><?php
			//echo "Version History:\n\n";
			readfile( $plugin_path . '/history.txt' );
		?></textarea>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#pluginbuddy_<?php echo $plugin_slug; ?>_debugtoggle").click(function() {
					jQuery("#pluginbuddy_<?php echo $plugin_slug; ?>_debugtoggle_div").slideToggle();
				});
			});
		</script>
		<?php
		if ( pb_backupbuddy::_POST( 'reset_defaults' ) == $plugin_slug ) {
			if ( call_user_func(  'pb_' . $plugin_slug . '::reset_options', true ) === true ) {
				pb_backupbuddy::alert( 'Plugin settings have been reset to defaults for plugin `' . $data['name'] . '`.' );
			} else {
				pb_backupbuddy::alert( 'Unable to reset plugin settings. Verify you are running the latest version.' );
			}
		}
		?>
	<?php pb_backupbuddy::$ui->end_metabox(); ?>
	
	<?php
}