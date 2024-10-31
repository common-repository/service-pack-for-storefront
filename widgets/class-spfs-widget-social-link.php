<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Social Link Widget.
 *
 * @class    SPFS_Widget_Social_Link
 * @since    0.0.1
 * @package  SPFS/Widgets
 * @category Widgets
 * @author   Opportus
 */
class SPFS_Widget_Social_Link extends WP_Widget {

  /**
   * Social Link Widget constructor.
   */
  public function __construct() {
    parent::__construct( 'spfs_widget_social_link', 'Social Link', array( 'description' => 'Social links widget' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
  }

  /**
   * Social Link Widget form.
   *
   * @param array $instance
   */
	public function form( $instance ) {
    $title = isset( $instance['title'] ) ? $instance['title'] : '';
    
    echo '<p><label for="' . esc_attr( $this->get_field_name( 'title' ) ) . '">' . esc_html__( 'Title:', 'service-pack-for-storefront' ) . '</label>';
		echo '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" /></p>';
	}

  /**
   * Social Link Widget template.
   *
   * @param array $args
   * @param array $instance
   */
	public function widget( $args, $instance ) {
    $options = get_option( 'spfs_settings' );

    echo $args['before_widget'];
		echo $args['before_title'];
		echo $instance['title'];
		echo $args['after_title'];
		
    echo '<div class="spfs-widget-social-link">';
    echo '<ul class="spfs-widget-social-link-list">';
    
    foreach ( $options['social_network'] as $network => $url ) {
      if ( ! is_null( $url ) ) {
        echo '<li><a class="spfs-widget-social-link-' . $network . '" rel="external" href="' . esc_url( $url ) .'" target="_blank"></a></li>';
      }
    }
    echo '</ul>';
    echo '</div>';
		echo $args['after_widget'];
  }

  /**
   * Social Link Widget stylesheet.
   *
   * Hooked into 'wp_enqueue_scripts' action.
   */
  public function enqueue_scripts() {
    wp_register_style( 'spfs-widget-social-link-style', SPFS_URL . 'assets/css/widget-social-link.min.css' );
    wp_enqueue_style( 'spfs-widget-social-link-style' );
  }
}
