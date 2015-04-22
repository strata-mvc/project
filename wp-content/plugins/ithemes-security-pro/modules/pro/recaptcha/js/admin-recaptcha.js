jQuery( document ).ready( function ( $ ) {

	$( "#itsec_recaptcha_enabled" ).change(function () {

		if ( $( "#itsec_recaptcha_enabled" ).is( ':checked' ) ) {

			$( "#recaptcha-settings" ).show();

		} else {

			$( "#recaptcha-settings" ).hide();

		}

	} ).change();

} );