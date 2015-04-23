jQuery( document ).ready( function () {

	jQuery( "#itsec_user_logging_enabled" ).change(function () {

		if ( jQuery( "#itsec_user_logging_enabled" ).is( ':checked' ) ) {

			jQuery( "#user_logging-settings" ).show();

		} else {

			jQuery( "#user_logging-settings" ).hide();

		}

	} ).change();

} );