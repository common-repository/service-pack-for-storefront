<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/*
 * Dynamic Sidebar module
 *
 * @class    SPFS_Dynamic_Sidebar
 * @since    0.0.1
 * @package  SPFS/Modules
 * @category Modules
 * @author   Opportus
 */
class SPFS_Dynamic_Sidebar {

  /**
   * Dynamic Sidebar module constructor.
   */
  public function __construct() {
    add_action( 'init', array( $this, 'storefront_remove_sidebar' ) );
    add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
    add_action( 'storefront_sidebar', array( $this, 'sidebar_template' ), 50 );
    add_action( 'storefront_footer', array( $this, 'top_footer_template' ), 5 );
  }

  /**
   * Remove original Storefront sidebar.
   *
   * Hooked into 'init' action.
   */
  public function storefront_remove_sidebar() {
    remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
  }

  /**
   * Dynamic Sidebar template.
   *
   * Hooked into 'storefront_sidebar' action.
   */
  public function sidebar_template() {
    $sidebar = null;

    if ( ! SPFS::get_instance()->is_missing_dependency( 'woocommerce' ) ) {
      if ( is_front_page() ) {
        $sidebar = 'sidebar-1';
      } elseif ( is_home() || is_category() || is_date() || is_single() && ! is_product() ) {
        $sidebar = 'sidebar-blog';
      } elseif ( is_product_category() || is_search() && is_woocommerce() ) {
        $sidebar = 'sidebar-product-category';
      } elseif ( is_product() ) {
        $sidebar = 'sidebar-product';
      } elseif ( is_page() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
        $sidebar = 'sidebar-page';
      } elseif ( is_cart() || is_checkout() || is_account_page() ) {
        $sidebar = 'sidebar-special';
      }
    }
    else {
      if ( is_front_page() ) {
        $sidebar = 'sidebar-1';
      } elseif ( is_home() || is_category() || is_date() || is_single()) {
        $sidebar = 'sidebar-blog';
      } elseif ( is_page() ) {
        $sidebar = 'sidebar-page';
      }
    }
    apply_filters( 'spfs_dynamic_sidebar_template', $sidebar );

    if ( ! is_active_sidebar( $sidebar ) ) {
      return;
    }
    echo '<div id="secondary" class="widget-area" role="complementary">';
    dynamic_sidebar( $sidebar );
    echo '</div>';
  }

  /**
   * Top Footer sidebar template.
   *
   * Hooked into 'storefront_footer' action.
   */
  public function top_footer_template() {
    $rows = apply_filters( 'spfs_dynamic_sidebar_top_footer_template_rows', 2 );
    $r    = 0;
    
    while ( $r < $rows ) {
      $r++;
      
      if ( is_active_sidebar( 'top-footer-' . $r . '-2' ) ) {
			  $columns = 2;
		  } elseif ( is_active_sidebar( 'top-footer-' . $r . '-1' ) ) {
			    $columns = 1;
		  } else {
			    $columns = 0;
      }
      apply_filters( 'spfs_dynamic_sidebar_top_footer_template_columns', $columns );

      if ( 0 < $columns ) {
        echo '<div class="footer-widgets col-' . intval( $columns ) . ' top-footer row-' . intval( $r ) . ' fix">';
        $c = 0;
        
        while ( $c < $columns ) {
          $c++;
      
          if ( is_active_sidebar( 'top-footer-' . $r . '-' . $c ) ) {
            echo '<section class="block footer-widget-' . intval( $c ) . '">';
					  dynamic_sidebar( 'top-footer-' . intval( $r ) . '-' . intval( $c ) );
					  echo '</section>';
          }
			  }
        echo '</div>';
      }
	  }
  }

  /**
   * Register sidebars.
   *
   * Hooked into 'widgets_init' action.
   */
  public function register_sidebars() {
    $sidebars = array(
      array(
		    'name'					=> __( 'Blog Sidebar', 'service-pack-for-storefront' ),
		    'id'						=> 'sidebar-blog',
		    'description' 	=> __( 'Sidebar displaying on your blog.', 'service-pack-for-storefront' ),
		    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		    'after_widget'	=> '</aside>',
		    'before_title'	=> '<h3 class="widget-title">',
		    'after_title' 	=> '</h3>'
	    ),
      array(
		    'name'					=> __( 'Page Sidebar', 'service-pack-for-storefront' ),
		    'id'						=> 'sidebar-page',
		    'description' 	=> __( 'Sidebar displaying on single pages.', 'service-pack-for-storefront' ),
		    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		    'after_widget'	=> '</aside>',
		    'before_title'	=> '<h3 class="widget-title">',
		    'after_title' 	=> '</h3>'
	    ),
      array(
		    'name'					=> __( 'Top Footer 1', 'service-pack-for-storefront' ),
		    'id'  					=> 'top-footer-1-1',
		    'description' 	=> __( 'Top footer row-1 column-1', 'service-pack-for-storefront' ),
		    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		    'after_widget'	=> '</aside>',
		    'before_title'	=> '<h3>',
		    'after_title' 	=> '</h3>'
	    ),
      array(
		    'name'					=> __( 'Top Footer 2', 'service-pack-for-storefront' ),
		    'id'  					=> 'top-footer-1-2',
		    'description' 	=> __( 'Top footer row-1 column-2', 'service-pack-for-storefront' ),
		    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		    'after_widget'	=> '</aside>',
		    'before_title'	=> '<h3>',
		    'after_title' 	=> '</h3>'
	    ),
      array(
		    'name'					=> __( 'Top Footer 3', 'service-pack-for-storefront' ),
		    'id'  					=> 'top-footer-2-1',
		    'description' 	=> __( 'Top footer row-2 column-1', 'service-pack-for-storefront' ),
		    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		    'after_widget'	=> '</aside>',
		    'before_title'	=> '<h3>',
		    'after_title' 	=> '</h3>'
	    ),
      array(
		    'name'					=> __( 'Top Footer 4', 'service-pack-for-storefront' ),
		    'id'  					=> 'top-footer-2-2',
		    'description' 	=> __( 'Top footer row-2 column-2', 'service-pack-for-storefront' ),
		    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		    'after_widget'	=> '</aside>',
		    'before_title'	=> '<h3>',
		    'after_title' 	=> '</h3>'
	    )
    );
    if ( ! SPFS::get_instance()->is_missing_dependency( 'woocommerce' ) ) {
      $sidebars_wc = array(
        array(
		      'name'					=> __( 'Product Category Sidebar', 'service-pack-for-storefront' ),
		      'id'						=> 'sidebar-product-category',
		      'description' 	=> __( 'Sidebar displaying on product categories.', 'service-pack-for-storefront' ),
		      'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		      'after_widget'	=> '</aside>',
		      'before_title'	=> '<h3 class="widget-title">',
		      'after_title' 	=> '</h3>'
	      ),
        array(
		      'name'					=> __( 'Product Sidebar', 'service-pack-for-storefront' ),
		      'id'						=> 'sidebar-product',
		      'description' 	=> __( 'Sidebar displaying on product page.', 'service-pack-for-storefront' ),
		      'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		      'after_widget'	=> '</aside>',
		      'before_title'	=> '<h3 class="widget-title">',
		      'after_title' 	=> '</h3>'
	      ),
        array(
		      'name'					=> __( 'Special Sidebar', 'service-pack-for-storefront' ),
		      'id'						=> 'sidebar-special',
		      'description' 	=> __( 'Sidebar displaying on my account, checkout, and cart pages.', 'service-pack-for-storefront' ),
		      'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		      'after_widget'	=> '</aside>',
		      'before_title'	=> '<h3 class="widget-title">',
		      'after_title' 	=> '</h3>'
	      )
      );
      $sidebars = array_merge( $sidebars_wc, $sidebars );
    }
    apply_filters( 'spfs_dynamic_sidebar_register', $sidebars );
    
    if ( ! is_array( $sidebars ) ) {
      return;
    }
    foreach ( $sidebars as $sidebar ) {
      if ( isset( $sidebar['id'] ) ) {
        register_sidebar( $sidebar );
      }
    }
  }
}
