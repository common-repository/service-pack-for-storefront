jQuery( document ).ready( function( $ ) {
	
	var windowWidth = parseFloat( window.innerWidth );
  
  $( 'body' ).addClass( 'spfs-floatable-menu' );

  if ( windowWidth >= 768 ) {
    
    var menuWidth = $( '.storefront-primary-navigation' ).width();
    var menuHeight = $( '.storefront-primary-navigation' ).height();
    var menuPadding = ( windowWidth - menuWidth ) / 2;
    var headerMargin = parseFloat( $( '.site-header' ).css( 'marginBottom' ) );
    var headerMarginNew = headerMargin + menuHeight;
    var menuColor = $( '.site-header' ).css( 'background-color' );
	  var position = $( '.storefront-primary-navigation' ).offset().top;
	  var scrolled = false;
	  
    $( window ).scroll( function() {
		
		  if ( ! scrolled ) {
			  if ( $( window ).scrollTop() > position ) {
          $( '.storefront-primary-navigation' ).addClass( 'spfs-float-menu' );
          $( '.spfs-float-menu' ).css( { 'padding-left': menuPadding, 'padding-right': menuPadding, 'background-color': menuColor } );
          $( '.site-header' ).css( 'margin-bottom', headerMarginNew );
				  scrolled = true;
			  }
		  }
		  if ( $( window ).scrollTop() <= position ) {
        $( '.spfs-float-menu' ).css( { 'padding-left': '', 'padding-right': '', 'background-color': '' } );
        $( '.site-header' ).css( 'margin-bottom', '' );
			  $( '.storefront-primary-navigation' ).removeClass( 'spfs-float-menu' ); 
			  scrolled = false;
		  }
	  } );
  }

  if ( windowWidth < 768 ) {
    
    var menuHeight = $( '.site-header' ).height();
    var menuMargin = parseFloat( $( '.site-header' ).css( 'marginBottom' ) );
    var contentPadding = menuHeight + menuMargin;

    $( '.site-header' ).css( 'margin-bottom', menuMargin );
    $( '.site-content' ).css( 'padding-top', contentPadding );
  }
} );
