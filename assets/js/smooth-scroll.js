jQuery( document ).ready( function( $ ) {
  
  // Get the window width for determining on which device we are.
  var windowWidth = window.innerWidth;
  // Check if the SSP Float Menu is enabled.
  var isFloatableMenu = $( 'body' ).hasClass( 'spfs-floatable-menu' );
  // Default wedge with or without the SSP floating menu.
  var wedge = 15;
  // Scrolling animation speed.
  var speed = 750;

  if ( isFloatableMenu ) {
    if ( windowWidth >= 768 ) {
      wedge += $( '.storefront-primary-navigation' ).height();
    }
    else {
      wedge += $( '.site-header' ).innerHeight();
    }
  }

  $( '.woocommerce-review-link' ).click( function() {
		var page = $( this ).attr( 'href' );
		$( 'html, body' ).animate( { scrollTop: $( page ).offset().top - wedge }, speed );
		return false;
	} );
} );
