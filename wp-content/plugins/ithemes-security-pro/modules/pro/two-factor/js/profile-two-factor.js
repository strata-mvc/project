jQuery( document ).ready( function ( $ ) {

	/**
	 * Generate an initial QR Code
	 */
	$( '#qrcode' ).qrcode( 'otpauth://totp/' + $( '#itsec_two_factor_description' ).val() + ':' + $( '#user_login' ).val() + "?secret=" + $( '#itsec_two_factor_key' ).val() + '&issuer=' + $( '#itsec_two_factor_description' ).val() );

	/**
	 * Shows the two-factor settings when two-factor is enabled
	 */
	$( "#itsec_two_factor_enabled" ).change( function () {

		if ( $( "#itsec_two_factor_enabled" ).is( ':checked' ) ) {

			$( "#itsec_two_factor_settings" ).show();

		} else {

			$( "#itsec_two_factor_settings" ).hide();

		}

	} ).change();

	/**
	 * Shows the app-password settings when the app password is enabled
	 */
	$( "#itsec_two_factor_use_app" ).change( function () {

		if ( $( "#itsec_two_factor_use_app" ).is( ':checked' ) ) {

			$( "#itsec_two_factor_app_pass_settings" ).show();

		} else {

			$( "#itsec_two_factor_app_pass_settings" ).hide();

		}

	} ).change();

	/**
	 * Process getting a new secret key
	 */
	$( '#itsec_two_factor_get_new_key' ).click( function ( event ) {

		event.preventDefault();

		var data = {
			action: 'itsec_two_factor_profile_ajax',
			nonce : itsec_two_factor_profile.nonce
		};

		//call the ajax
		$.ajax(
			{
				url     : ajaxurl,
				type    : 'POST',
				data    : data,
				complete: function ( response ) {

					$( '#itsec_two_factor_key' ).val( response.responseText );
					$( '#qrcode' ).empty().qrcode( 'otpauth://totp/' + $( '#itsec_two_factor_description' ).val() + ':' + $( '#user_login' ).val() + "?secret=" + $( '#itsec_two_factor_key' ).val() + '&issuer=' + $( '#itsec_two_factor_description' ).val() );

				}
			}
		);

	} );

	/**
	 * Process getting a new app password
	 */
	$( '#itsec_two_factor_get_new_app_pass' ).click( function ( event ) {

		event.preventDefault();

		$( '#app-inputs-form' ).show();

		var data = {
			action: 'itsec_two_factor_profile_new_app_pass_ajax',
			nonce : itsec_two_factor_profile.nonce
		};

		//call the ajax
		$.ajax(
			{
				url     : ajaxurl,
				type    : 'POST',
				data    : data,
				complete: function ( response ) {

					$( '#itsec_new_app_pass_pass' ).val( response.responseText );

				}
			}
		);

	} );

} );

/**
 * Use Backbone.js passed to jQuery to handle app passwords
 */
(function ( $ ) {

	var passwordCount = 0;

	/**
	 * Model for individual app passwords
	 */
	var AppPassword = Backbone.Model.extend(
		{
			defaults: {
				id  : null, //numeric based on array position
				name: null, //string - readable name
				pass: null //string the password itself (or dashes after save)
			}

		}
	);

	/**
	 * Collection of all available app passwords
	 */
	var AppPasswords = Backbone.Collection.extend(
		{
			model: AppPassword
		}
	);

	/**
	 * Displays individual app password
	 */
	var PassView = Backbone.View.extend(
		{
			tagName : 'tr',
			el      : '#app-passwords',
			template: wp.template( 'app-inputs-template' ),

			render: function () {

				$( this.el ).append( this.template( this.model.toJSON() ) );
				return this;

			}

		}
	);

	/**
	 * Displays list of app passwords
	 */
	var ListView = Backbone.View.extend(
		{
			el        : '#app-inputs',
			template  : wp.template( 'app-inputs-template' ),
			initialize: function () {

				var passwordData = JSON.parse( itsec_two_factor_profile.passwords );

				passwordCount = passwordData.length;

				_.bindAll( this, 'render' );
				this.collection = new AppPasswords( passwordData );
				this.render();
				this.collection.on( 'add', this.renderPass, this );

			},
			events    : {
				'click #itsec-app-pass-add-new': 'addPass',
				'click .delete-button'         : 'deletePass'
			},

			addPass: function ( event ) {

				event.preventDefault();

				passwordCount ++;

				var passName = $( event.target ).siblings( '#itsec_new_app_pass_name' ).val();
				var pass = $( event.target ).siblings( '#itsec_new_app_pass_pass' ).val();

				if ( passName.trim().length < 1 ) {
					alert( itsec_two_factor_profile.bad_name );
					return;
				}

				var newModel = {

					id  : passwordCount,
					name: passName,
					pass: pass

				}

				this.collection.add( new AppPassword( newModel ) );

				$( '#app-inputs-form' ).hide();
				$( '#itsec_new_app_pass_pass' ).val( '' );

				$( '#app-inputs-form' ).after( '<div class="dialog" id="itsec-app-password-results" style="float: none; margin: 13px 5px 5px 1px;"><p>' + itsec_two_factor_profile.dialog_text1 + ' <strong>' + passName + '</strong> ' + itsec_two_factor_profile.dialog_text2 + itsec_two_factor_profile.dialog_text3 + '</p><h2 style="text-align: center">' + pass + '</h2></div>' );

				$( '#itsec-app-password-results' ).dialog(
					{
						dialogClass  : 'wp-dialog isec_strong_password',
						modal        : true,
						closeOnEscape: false,
						title        : itsec_two_factor_profile.dialog_title + ' ' + passName,
						width        : '50%',
						resizable    : false,
						draggable    : false
					}
				);

			},

			/**
			 * Removes the app password upon confirmation
			 *
			 * @param pass the DOM object of the removed password
			 */
			deletePass: function ( pass ) {

				var passToRemove = ( this.collection.get( $( pass.target ).siblings( '.pass-id' ).val() ));

				this.collection.remove( passToRemove );

				$( pass.target ).closest( '.app-password' ).remove();

			},

			render: function () {

				var self = this;

				this.collection.each(
					function ( item ) {
						self.renderPass( item );
					}
				);

			},

			renderPass: function ( item ) {

				var passItem = new PassView( { model: item } );
				$( this.el ).append( passItem.render().$( this.el ) );

			}

		}
	);

	var listView = new ListView(); //initialize app passwords

}( jQuery ) );