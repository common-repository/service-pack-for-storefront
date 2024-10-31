jQuery( document ).ready( function( $ ) {
	
	if ( $( '#spfs-contact-form-send' ).length > 0 ) {
		
		var busy  = null;
		
		$( '#spfs-contact-form-send' ).click( function() {
			
			var form  = $( this ).closest( 'form' ),
					error = null,
					message = null;
					
			if ( busy ) busy.abort();
			busy = $.ajax( {
				url: spfs_contact_form_ajax.url,
				type: 'POST',
				data: form.serialize(),
				success: function( response ) {
					
					$( '<div id="spfs-contact-form-noty"></div>' ).insertAfter( '#spfs-contact-form' );
					
					if ( response.success === true ) {
						$( '#spfs-contact-form-noty' ).attr( 'class', 'spfs-contact-form-noty-success' ).html( response.data );
						form.find( '[required]' ).each( function() {
							$( this ).removeAttr( 'style' );
						} );
						form[0].reset();
					}
					else {
						$( '#spfs-contact-form-noty' ).attr( 'class', 'spfs-contact-form-noty-error' ).html( response.data );
						form.find( '[required]' ).each( function() {
						
							if( $.trim( $( this ).val() ) === '' ) {
								$( this ).css( 'border-color', '#FF0000' );
							}
							else {
								$( this ).css( 'border-color', '#44a62b' );
							}
						} );
					}
					$( '#spfs-contact-form-noty' ).slideDown();
				}
			} );
			return false;
		} );
	}
} );
