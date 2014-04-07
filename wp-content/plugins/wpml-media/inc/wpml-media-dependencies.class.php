<?php
class WPML_Media_Dependencies
{
	function __construct()
	{
	}

	function check()
	{
		$all_ok = true;

		// Check if WPML is active. If not display warning message and don't load WPML-media
		if ( !defined( 'ICL_SITEPRESS_VERSION' ) || ICL_PLUGIN_INACTIVE )
		{
			add_action( 'admin_notices', array( $this, '_no_wpml_warning' ) );

			$all_ok = false;
		} elseif ( version_compare( ICL_SITEPRESS_VERSION, '2.0.5', '<' ) )
		{
			add_action( 'admin_notices', array( $this, '_old_wpml_warning' ) );

			$all_ok = false;
		}

		if ( !$all_ok )
		{
			return false;
		}

		return true;
	}

	function _no_wpml_warning()
	{
		?>
		<div class="message error"><p><?php printf( __( 'WPML Media is enabled but not effective. It requires <a href="%s">WPML</a> in order to work.', 'wpml-translation-management' ),
													'http://wpml.org/' ); ?></p></div>
	<?php
	}

	// Localization

	function _old_wpml_warning()
	{
		?>
		<div class="message error"><p><?php printf( __( 'WPML Media is enabled but not effective. It is not compatible with  <a href="%s">WPML</a> versions prior 2.0.5.', 'wpml-translation-management' ),
													'http://wpml.org/' ); ?></p></div>
	<?php
	}

}