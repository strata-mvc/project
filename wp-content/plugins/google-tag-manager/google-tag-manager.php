<?php
/*
Plugin Name: Google Tag Manager
Plugin URI: http://wordpress.org/extend/plugins/google-tag-manager/
Description: This is an implementation of the new Tag Management system from Google. It adds a field to the existing General Settings page for the ID, and if specified, outputs the tag management javascript in the page footer.
Version: 1.0
Author: George Stephanis
Author URI: http://Stephanis.info
License: GPLv2 or later
*/

class google_tag_manager {

	function go() {
		add_filter( 'admin_init', array( __CLASS__, 'register_fields' ) );
		add_action( 'wp_footer', array( __CLASS__, 'print_tag' ) );
	}
	function register_fields() {
		register_setting( 'general', 'google_tag_manager_id', 'esc_attr' );
		add_settings_field( 'google_tag_manager_id', '<label for="google_tag_manager_id">' . __( 'Google Tag Manager ID' , 'google_tag_manager' ) . '</label>' , array( __CLASS__, 'fields_html') , 'general' );
	}
	function fields_html() {
		?>
		<input type="text" id="google_tag_manager_id" name="google_tag_manager_id" placeholder="ABC-DEFG" class="regular-text code" value="<?php echo get_option( 'google_tag_manager_id', '' ); ?>" />
		<p class="description"><?php _e( 'The ID from Google&rsquo;s provided code (as emphasized):', 'google_tag_manager' ); ?><br />
			<code>&lt;noscript&gt;&lt;iframe src="//www.googletagmanager.com/ns.html?id=<strong style="color:#c00;">ABC-DEFG</strong>"</code></p>
		<p class="description"><?php _e( 'You can get yours <a href="https://www.google.com/tagmanager/">here</a>!', 'google_tag_manager' ); ?></p>
		<?php
	}
	function print_tag() {
		if( ! $id = get_option( 'google_tag_manager_id', '' ) ) return;
		?>
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo $id; ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $id; ?>');</script>
<!-- End Google Tag Manager -->
		<?php
	}
}

google_tag_manager::go();