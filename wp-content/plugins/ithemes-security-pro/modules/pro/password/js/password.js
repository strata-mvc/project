jQuery( document ).ready( function () {

	if ( itsec_password_generator.generator == 1 ) {

		//Insert the button via jQuery so we can put it where we want.
		jQuery( '#pass-strength-result' ).after( '<a href="itsec_generate_strong_password_results" class="dialog button-primary" id="itsec_generate_strong_password" style="float: none; margin: 13px 5px 5px 1px;">' + itsec_password_generator.text1 + '</a>' );

		//process password generator button
		jQuery( '#itsec_generate_strong_password' ).live( 'click', function ( event ) {

			event.preventDefault();

			var new_pass = random_password( itsec_password_generator.base_length );

			jQuery( '#pass1, #pass2' ).val( new_pass ); //set the values

			check_pass_strength(); //updates the meter

			jQuery( '#itsec_generate_strong_password' ).after( '<div id="itsec_generate_strong_password_results" style="display: hidden"><p>' + itsec_password_generator.text2 + '</p><span style="background-color: #ffffcb; padding: 10px 0 10px 0; font-size: 150%; display: block; width: 100%; text-align: center; font-weight: bold; font-style: italic;">' + new_pass + '</span></div>' ); //generate the password modal information

			//setup the password modal
			jQuery( '#itsec_generate_strong_password_results' ).dialog(
				{
					dialogClass  : 'wp-dialog isec_strong_password',
					modal        : true,
					closeOnEscape: false,
					title        : itsec_password_generator.text3,
					width        : '50%',
					resizable    : false,
					draggable    : false
				}
			);

		} );

	}

	if ( itsec_password_generator.password_expired == 1 ) {

		alert( itsec_password_generator.text4 );

	}

} );

var random_password = function ( base_length ) {

	var base = parseInt( base_length );
	var length = get_random_with_bounds( base, ( base + 10 ) );
	var text = '';
	var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=;';

	for ( var i = 0; i < length; i ++ ) {
		text += possible.charAt( Math.floor( Math.random() * possible.length ) );
	}

	return text;
}

var get_random_with_bounds = function ( min, max ) {

	return parseInt( Math.random() * ( max - min ) + min );

}

//Imported from WordPress as we need to run it again
function check_pass_strength() {

	var pass1 = jQuery( '#pass1' ).val(), pass2 = jQuery( '#pass2' ).val(), strength;

	jQuery( '#pass-strength-result' ).removeClass( 'short bad good strong' );

	if ( ! pass1 ) {
		jQuery( '#pass-strength-result' ).html( pwsL10n.empty );
		return;
	}

	strength = wp.passwordStrength.meter( pass1, wp.passwordStrength.userInputBlacklist(), pass2 );

	switch ( strength ) {
		case 2:
			jQuery( '#pass-strength-result' ).addClass( 'bad' ).html( pwsL10n.bad );
			break;
		case 3:
			jQuery( '#pass-strength-result' ).addClass( 'good' ).html( pwsL10n.good );
			break;
		case 4:
			jQuery( '#pass-strength-result' ).addClass( 'strong' ).html( pwsL10n.strong );
			break;
		case 5:
			jQuery( '#pass-strength-result' ).addClass( 'short' ).html( pwsL10n.mismatch );
			break;
		default:
			jQuery( '#pass-strength-result' ).addClass( 'short' ).html( pwsL10n['short'] );
	}

}