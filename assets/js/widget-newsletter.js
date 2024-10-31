jQuery( document ).ready( function( $ ) {
	
	if ( $( '.spfs-widget-newsletter-send' ).length > 0 ) {
		
		var busy  = null;
		
		$( '.spfs-widget-newsletter-send' ).click( function() {
			
			var form  = $( this ).closest( 'form' );
			
			if ( busy ) busy.abort();
			busy = $.ajax( {
				url: spfs_widget_newsletter_ajax.url,
				type: 'POST',
				data: form.serialize(),
				success: function( response ) {
					
					$( 'body' ).append( '<div id="spfs-widget-newsletter-noty"></div>' );
					
					if ( response.success === true ) {
						$( '#spfs-widget-newsletter-noty' ).attr( 'class', 'spfs-widget-newsletter-noty-success' ).html( response.data );
						form[0].reset();
					}
					else {
						$( '#spfs-widget-newsletter-noty' ).attr( 'class', 'spfs-widget-newsletter-noty-error' ).html( response.data );
					}
					$( '#spfs-widget-newsletter-noty' ).slideDown();
					$( '#spfs-widget-newsletter-noty' ).delay( 5000 ).slideUp();
				}
			} );
			
			return false;
		} );
	}
} );
