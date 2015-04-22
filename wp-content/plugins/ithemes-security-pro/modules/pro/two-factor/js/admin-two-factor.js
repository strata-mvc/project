jQuery( document ).ready( function ( $ ) {

	/**
	 * Show two-factor settings when two-factor is enabled
	 */
	$( "#itsec_two_factor_enabled" ).change( function () {

		if ( $( "#itsec_two_factor_enabled" ).is( ':checked' ) ) {

			$( "#two_factor-settings" ).show();

		} else {

			$( "#two_factor-settings" ).hide();

		}

	} ).change();

} );