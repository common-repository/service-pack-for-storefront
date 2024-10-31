jQuery( document ).ready( function( $ ) {

	$( '.widget_layered_nav, .widget_price_filter' ).wrapAll( '<div class="spfs_widget_layered_nav_wrapper">' );
	$( '.widget_layered_nav' ).first().addClass( 'first' );
} );
