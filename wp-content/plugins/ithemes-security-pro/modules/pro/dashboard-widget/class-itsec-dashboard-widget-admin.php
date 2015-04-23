<?php

class ITSEC_Dashboard_Widget_Admin {

	private
		$core,
		$module_path;

	function run( $core ) {

		$this->core        = $core;
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'admin_init', array( $this, 'admin_init' ) );

	}

	/**
	 * Show link to logs
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	private function access_logs() {

		echo '<ul>
				<li><a href="admin.php?page=itsec">' . __( '> Plugin Dashboard', 'it-l10n-ithemes-security-pro' ) . '</a></li>
				<li><a href="admin.php?page=toplevel_page_itsec_logs">' . __( '> View Security Logs', 'it-l10n-ithemes-security-pro' ) . '</a></li>
			</ul>';
	}

	/**
	 * Execute all hooks on admin init
	 *
	 * All hooks on admin init to make certain user has the correct permissions
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function admin_init() {

		global $itsec_globals;

		if ( ( ! is_multisite() && current_user_can( $itsec_globals['plugin_access_lvl'] ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); //enqueue scripts for admin page
			add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
			add_action( 'wp_ajax_itsec_release_dashboard_lockout', array( $this, 'itsec_release_dashboard_lockout' ) );
			add_action( 'wp_ajax_itsec_dashboard_summary_postbox_toggle', array( $this, 'itsec_dashboard_summary_postbox_toggle' ) );

		}
	}

	/**
	 * Add malware scheduling admin Javascript
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && ( strpos( get_current_screen()->id, 'dashboard' ) !== false ) ) {

			wp_enqueue_script( 'itsec_dashboard_widget_js', $this->module_path . 'js/admin-dashboard-widget.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

			wp_register_style( 'itsec_dashboard_widget_css', $this->module_path . 'css/admin-dashboard-widget.css', array(), $itsec_globals['plugin_build'] ); //add multi-select css
			wp_enqueue_style( 'itsec_dashboard_widget_css' );

			wp_localize_script( 'itsec_dashboard_widget_js', 'itsec_dashboard_widget_js', array(
				'host'          => '<p>' . __( 'Currently no hosts are locked out of this website.', 'it-l10n-ithemes-security-pro' ) . '</p>',
				'user'          => '<p>' . __( 'Currently no users are locked out of this website.', 'it-l10n-ithemes-security-pro' ) . '</p>',
				'postbox_nonce' => wp_create_nonce( 'itsec_dashboard_summary_postbox_toggle' ),
			) );

		}

	}

	/**
	 * Echo dashboard widget content
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function dashboard_widget_content() {

		$white_class = '';

		if ( function_exists( 'wp_get_current_user' ) ) {

			$current_user = wp_get_current_user();

			$meta = get_user_meta( $current_user->ID, 'itsec_dashboard_widget_status', true );

			if ( is_array( $meta ) ) {

				if ( isset( $meta['itsec_lockout_summary_postbox'] ) && $meta['itsec_lockout_summary_postbox'] == 'close' ) {
					$white_class = ' closed';
				}
			}

		}

		//Access Logs
		echo '<div class="itsec_links widget-section clear">';
		$this->access_logs();
		echo '</div>';

		//Whitelist
		echo '<div class="itsec_summary_widget widget-section clear postbox' . $white_class . '" id="itsec_lockout_summary_postbox">';
		$this->lockout_summary();
		echo '</div>';

		//Whitelist
		echo '<div class="itsec_whitelist_widget widget-section clear">';
		$this->self_protect_metabox();
		echo '</div>';

		//Run Malware Scan

		$malware = get_site_option( 'itsec_malware' );

		if ( isset( $malware['enabled'] ) && $malware['enabled'] === true ) {

			echo '<div class="itsec_malware_widget widget-section ">';
			$this->malware_scan();
			echo '</div>';

		}

		//Run file-change Scan

		$file_change = get_site_option( 'itsec_file_change' );

		if ( isset( $file_change['enabled'] ) && $file_change['enabled'] === true ) {

			echo '<div class="itsec_file-change_widget widget-section ">';
			$this->file_scan();
			echo '</div>';

		}

		//Show lockouts table
		echo '<div class="itsec_lockouts_widget widget-section clear">';
		$this->lockout_metabox();
		echo '</div>';

	}

	/**
	 * Show file scan button
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	private function file_scan() {

		$file_settings = get_site_option( 'itsec_file_change' );

		if ( isset( $file_settings['enabled'] ) && $file_settings['enabled'] === true ) {

			echo '<form id="itsec_one_time_file_check" method="post" action="">';
			echo wp_nonce_field( 'itsec_do_file_check', 'wp_nonce' );
			echo '<input type="hidden" name="itsec_file_change_origin" value="">';
			echo '<p>' . __( "If changes are found you will be taken to the logs page for details.", 'it-l10n-ithemes-security-pro' ) . '</p>';
			echo '<p><input type="submit" id="itsec_one_time_file_check_submit" class="button-primary" value="' . ( isset( $file_settings['split'] ) && $file_settings['split'] === true ? __( 'Scan Next File Chunk', 'it-l10n-ithemes-security-pro' ) : __( 'Scan Files Now', 'it-l10n-ithemes-security-pro' ) ) . '" /></p>';
			echo '</form>';

		}

	}

	/**
	 * Active lockouts table and form for dashboard.
	 *
	 * @Since 1.9
	 *
	 * @return void
	 */
	private function lockout_metabox() {

		global $itsec_lockout;

		$host_class = '';
		$user_class = '';

		if ( function_exists( 'wp_get_current_user' ) ) {

			$current_user = wp_get_current_user();

			$meta = get_user_meta( $current_user->ID, 'itsec_dashboard_widget_status', true );

			if ( is_array( $meta ) ) {

				if ( isset( $meta['itsec_lockout_host_postbox'] ) && $meta['itsec_lockout_host_postbox'] == 'close' ) {
					$host_class = ' closed';
				}

				if ( isset( $meta['itsec_lockout_user_postbox'] ) && $meta['itsec_lockout_user_postbox'] == 'close' ) {
					$user_class = ' closed';
				}
			}

		}

		//get locked out hosts and users from database
		$host_locks = $itsec_lockout->get_lockouts( 'host', true, 100 );
		$user_locks = $itsec_lockout->get_lockouts( 'user', true, 100 );
		?>
		<div class="postbox<?php echo $host_class; ?>" id="itsec_lockout_host_postbox">
			<div class="handlediv" title="Click to toggle"><br/></div>
			<h4 class="dashicons-before dashicons-lock"><?php _e( 'Locked out hosts', 'it-l10n-ithemes-security-pro' ); ?></h4>

			<div class="inside">
				<?php if ( sizeof( $host_locks ) > 0 ) { ?>

					<ul>
						<?php foreach ( $host_locks as $host ) { ?>

							<li>
								<label for="lo_<?php echo $host['lockout_id']; ?>">
									<?php printf( '<a target="_blank" href="http://ip-adress.com/ip_tracer/%s">%s</a>', filter_var( $host['lockout_host'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ), filter_var( $host['lockout_host'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ); ?>
									<a href="<?php echo wp_create_nonce( 'itsec_reloease_dashboard_lockout' . $host['lockout_id'] ); ?>"
									   id="<?php echo $host['lockout_id']; ?>"
									   class="itsec_release_lockout locked_host"><span
											class="itsec-locked-out-remove">&mdash;</span></a>
								</label>
							</li>

						<?php } ?>
					</ul>

				<?php } else { //no host is locked out ?>

					<p><?php _e( 'Currently no hosts are locked out of this website.', 'it-l10n-ithemes-security-pro' ); ?></p>

				<?php } ?>
			</div>
		</div>
		<div class="postbox<?php echo $user_class; ?>" id="itsec_lockout_user_postbox">
			<div class="handlediv" title="Click to toggle"><br/></div>
			<h4 class="dashicons-before dashicons-admin-users"><?php _e( 'Locked out users', 'it-l10n-ithemes-security-pro' ); ?></h4>

			<div class="inside">
				<?php if ( sizeof( $user_locks ) > 0 ) { ?>
					<ul>
						<?php foreach ( $user_locks as $user ) { ?>

							<?php $userdata = get_userdata( $user['lockout_user'] ); ?>

							<li>
								<label for="lo_<?php echo $user['lockout_id']; ?>">

									<a href="<?php echo wp_create_nonce( 'itsec_reloease_dashboard_lockout' . $user['lockout_id'] ); ?>"
									   id="<?php echo $user['lockout_id']; ?>"
									   class="itsec_release_lockout locked_user"><span
											class="itsec-locked-out-remove">&mdash;</span><?php echo isset( $userdata->user_login ) ? $userdata->user_login : ''; ?>
									</a>
								</label>
							</li>

						<?php } ?>
					</ul>
				<?php } else { //no user is locked out ?>

					<p><?php _e( 'Currently no users are locked out of this website.', 'it-l10n-ithemes-security-pro' ); ?></p>

				<?php } ?>
			</div>
		</div>
	<?php
	}

	/**
	 * Process the ajax call for opening and closing postboxes
	 *
	 * @since 1.9
	 *
	 * @return string json string for success or failure
	 */
	public function itsec_dashboard_summary_postbox_toggle() {

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_dashboard_summary_postbox_toggle' ) ) {
			die ( __( 'Security error', 'it-l10n-ithemes-security-pro' ) );
		}

		$id        = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : false;
		$direction = isset( $_POST['direction'] ) ? sanitize_text_field( $_POST['direction'] ) : false;

		if ( $id === false || $direction === false || ! function_exists( 'wp_get_current_user' ) || ! function_exists( 'get_user_meta' ) ) {
			die( false );
		}

		$current_user = wp_get_current_user();

		$meta = get_user_meta( $current_user->ID, 'itsec_dashboard_widget_status', true );

		if ( ! is_array( $meta ) ) {

			$meta = array(
				$id => $direction,
			);

		} else {

			$meta[$id] = $direction;

		}

		update_user_meta( $current_user->ID, 'itsec_dashboard_widget_status', $meta );

		die( true );

	}

	/**
	 * Process the ajax call for releasing lockouts from the dashboard
	 *
	 * @since 1.9
	 *
	 * @return string json string for success or failure
	 */
	public function itsec_release_dashboard_lockout() {

		global $itsec_lockout;

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_reloease_dashboard_lockout' . sanitize_text_field( $_POST['resource'] ) ) ) {
			die ( __( 'Security error', 'it-l10n-ithemes-security-pro' ) );
		}

		die( $itsec_lockout->release_lockout( absint( $_POST['resource'] ) ) );

	}

	/**
	 * Show a summary of lockouts in the database
	 *
	 * @Since 1.9
	 *
	 * @return void
	 */
	private function lockout_summary() {

		global $itsec_lockout;

		$lockouts = $itsec_lockout->get_lockouts( 'all' );
		$current  = sizeof( $itsec_lockout->get_lockouts( 'host', true ) ) + sizeof( $itsec_lockout->get_lockouts( 'user', true ) );
		echo '
				<div class="handlediv" title="Click to toggle"><br /></div>
					<h4 class="dashicons-before dashicons-shield-alt">' . __( 'Security Summary', 'it-l10n-ithemes-security-pro' ) . '</h4>
					<div class="inside">
						
						<div class="summary-item">
							<h5>' . __( 'Times protected from attack.', 'it-l10n-ithemes-security-pro' ) . '</h5>
							<span class="summary-total">' . sizeof( $lockouts ) . '</span>
						</div>
						
						<div class="summary-item">
						    <h5>' . __( 'Current Number of lockouts.', 'it-l10n-ithemes-security-pro' ) . '</h5>
						    <span class="summary-total" id="current-itsec-lockout-summary-total">' . $current . '</span>
					  	</div>
				   </div>
			';

	}

	/**
	 * Show malware scan button
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	private function malware_scan() {

		$malware_settings = get_site_option( 'itsec_malware' );

		if ( isset( $malware_settings['enabled'] ) && $malware_settings['enabled'] === true ) {

			echo '<form id="itsec_one_time_malware_scan" method="post" action="">';
			echo wp_nonce_field( 'itsec_do_malware_scan', 'wp_nonce' );
			echo '<input type="hidden" name="itsec_malware_scan_origin" value="">';
			echo '<p>' . __( "If malware is found you will be taken to the logs page for details.", 'it-l10n-ithemes-security-pro' ) . '</p>';
			echo '<p><input type="submit" id="itsec_one_time_malware_scan_submit" class="button-primary" value="' . __( 'Scan for Malware', 'it-l10n-ithemes-security-pro' ) . '" /></p>';
			echo '</form>';

		}

	}

	/**
	 * Active lockouts table and form for dashboard.
	 *
	 * @Since 1.9
	 *
	 * @return void
	 */
	private function self_protect_metabox() {

		global $itsec_globals;

		$temp = get_site_option( 'itsec_temp_whitelist_ip' );

		if ( $temp !== false ) {

			echo '<p class="itsec_temp_whitelist submit">';

			echo '<a href="#" class="itsec_temp_whitelist_release_ajax button-primary dashboard-whitelist">' . __( 'Remove IP from Whitelist', 'it-l10n-ithemes-security-pro' ) . '</a>';
			echo __( '<span class="itsec_temp_whitelist_ip">Your IP Address', 'it-l10n-ithemes-security-pro' ) . ', <strong>' . $temp['ip'] . '</strong>, ' . __( 'is whitelisted for', 'it-l10n-ithemes-security-pro' ) . ' <strong>' . human_time_diff( $itsec_globals['current_time'], $temp['exp'] ) . '</strong>.</span>';

			echo '</p>';

		} else {

			echo '<p class="itsec_temp_whitelist submit"><a href="#" class="itsec_temp_whitelist_ajax button-primary dashboard-whitelist">' . __( 'Temporarily Whitelist my IP', 'it-l10n-ithemes-security-pro' ) . '</a></p>';

		}

	}

	/**
	 * Create dashboard widget
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function wp_dashboard_setup() {

		global $itsec_globals;

		wp_add_dashboard_widget(
			'itsec-dashboard-widget',
			$itsec_globals['plugin_name'],
			array( $this, 'dashboard_widget_content' )
		);

	}

}