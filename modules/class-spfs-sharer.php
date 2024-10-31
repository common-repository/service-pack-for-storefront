<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Sharer Module
 *
 * @class    SPFS_Sharer
 * @since    0.0.1
 * @package  SPFS/Modules
 * @category Modules
 * @author   Opportus
 * @todo     Add more sharers.
 */
class SPFS_Sharer {

  /**
   * Sharer Module constructor.
   */
  public function __construct() {
    add_action( 'storefront_single_post', array( $this, 'template' ), 50 );
    add_action( 'woocommerce_share', array ( $this, 'template' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
  }

  /**
   * Sharer Module template.
   *
   * Hooked into 'storefront_single_post' and 'woocommerce_share' actions.
   */
  public function template() {  
    $sharers = array(
      array(
        'slug' => 'facebook',
        'url'  => 'https://www.facebook.com/sharer/sharer.php?u='
      ),
      array(
        'slug' => 'twitter',
        'url'  => 'https://twitter.com/intent/tweet?url='
      ),
      array(
        'slug' => 'google+',
        'url'  => 'https://plus.google.com/share?url='
      )
    );
    apply_filters( 'spfs_sharer_list', $sharers );
    
    if ( ! is_array( $sharers ) || ! isset( $sharers ) ) {
      return;
    }
    echo '<ul class="spfs-sharer">';

    $sharers_number = count( $sharers );
    $sharers_count = 0;
    
    foreach ( $sharers as $sharer ) {
      $sharers_count ++;
      $first = $sharers_count === 1 ? ' first' : '';

      echo '<li class="spfs-sharer-' . esc_attr( $sharer['slug'] ) . '-button' . $first . '">';
      echo '<a class="spfs-sharer-' . esc_attr( $sharer['slug'] ) . '-link" rel="nofollow" href="' . esc_url( $sharer['url'] ) . get_the_permalink() . '" title="' . sprintf( esc_attr__( 'Share on %s...', 'service-pack-for-storefront' ), $network = esc_attr( ucfirst( $sharer['slug'] ) ) ) . '" target="_blank"><span>' . esc_html( ucfirst( $sharer['slug'] ) ) . '</span></a>';
      echo '</li>';
    }
    echo '</ul>';
  }

  /**
   * Sharer Module stylesheet
   *
   * Hooked into 'wp_enqueue_scripts.'
   */
  public function enqueue_scripts() {
    wp_register_style( 'spfs-sharer-style', SPFS_URL . 'assets/css/sharer.min.css' );
		wp_enqueue_style( 'spfs-sharer-style' );
  }
}
