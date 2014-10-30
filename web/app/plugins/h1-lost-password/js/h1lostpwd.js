( function( $ ) {

	$( document ).ready( function() {

		h1LostPassword.init();

	});

	var h1LostPassword = {};


	h1LostPassword.init = function() {

		$( 'form#lostpasswordform' ).submit( function(e) {

			var $form = $( 'form#lostpasswordform' );

			// Get values
			var inputData = {
				action:     'h1lostpwd_lost_password',
				user_login: $( '#user_login', $form ).val(),
			};

			var h1lostpwdResult = $.post( ajax_url, inputData, null, 'json' );

			h1lostpwdResult.done( function( p ) {

				h1LostPassword.clearForm( $form );
				h1LostPassword.clearErrors( $form );

				if ( 'success' === p.status ) {
					$form.append( '<div class="h1lostpwd-success">' + p.message + '</div>' );
					$( '.h1lostpwd-field', $form ).hide();
				}

				var error = {};

				if ( 'error' === p.status ) {
					$.each( p.message, function( i, v ) {
						error.field = 'user_login';
						error.message = v;
						h1LostPassword.attachErrors( error, $form );
					});
				}

			});

			h1lostpwdResult.fail( function() { });

			e.preventDefault();
		});


		$( 'form#resetpassform' ).submit( function(e) {

			var $form = $( 'form#resetpassform' );

			// Get values
			var inputData = {
				action: 'h1lostpwd_reset_password',
				login:  $( '#user_login', $form ).val(),
				key:    $( '#key', $form ).val(),
				pass1:  $( '#pass1', $form ).val(),
				pass2:  $( '#pass2', $form ).val()
			};

			var h1resetpwdResult = $.post( ajax_url, inputData, null, 'json' );

			h1resetpwdResult.done( function( p ) {

				h1LostPassword.clearForm( $form );
				h1LostPassword.clearErrors( $form );

				if ( 'success' === p.status ) {
					$form.append( '<div class="h1lostpwd-success">' + p.message + '</div>' );
					$( '.h1lostpwd-field', $form ).hide();
					$( '.reset-pass', $form ).hide();
				}

				var error = {};

				if ( 'error' === p.status ) {

					$.each( p.message, function( i, v ) {
						error.field = i;
						error.message = v;
						h1LostPassword.attachErrors( error, $form );
					});
				}

			});

			h1resetpwdResult.fail( function() { });

			e.preventDefault();
		});

	};


	/**
	 * Add errors to the form fields.
	 * 
	 * @param  object error Object with error fields and error message.
	 * @param  object $form Form object.
	 */
	h1LostPassword.attachErrors = function( error, $form ) {

		if( 'key' == error.field ) {
			$( '.key-message', $form ).html( error.message );
		}

		var $field = $( '#' + error.field, $form );
		$field.parents( '.h1lostpwd-field' ).addClass( 'error' );
		$field.parents( '.h1lostpwd-field' ).find( 'span.message' ).html( error.message );

	};


	/**
	 * Clear errors.
	 */
	h1LostPassword.clearErrors = function( $form ) {

		$( '.h1lostpwd-success' ).remove();
		$( '.key-message' ).html();
		$form.find( 'span.message' ).html('');

		$( '.h1lostpwd-field' ).each( function( i, v ){
			$( v ).removeClass( 'error' );
			$( v ).find( '.message' ).html( '' );
		});

	};


	/**
	 * Clear form values.
	 */
	h1LostPassword.clearForm = function( $form ) {
		$( 'input', $form ).not( ':input[type=submit],:input[type=hidden]' ).val( '' );
	};

})( jQuery );