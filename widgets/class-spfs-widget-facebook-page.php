<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Facebook Page Widget.
 *
 * @class    SPFS_Widget_Facebook_Page
 * @since    0.0.1
 * @package  SPFS/Widgets
 * @category Widgets
 * @author   Opportus
 */
class SPFS_Widget_Facebook_Page extends WP_Widget {

  /**
   * Facebook Page Widget constructor.
   */
  public function __construct() {
    parent::__construct( 'spfs_widget_facebook_page', 'Facebook Page', array( 'description' => 'Facebook Page Widget' ) );
  }

  /**
   * Facebook Page Widget form.
   *
   * @param array $instance
   */
	public function form( $instance ) {
    $title = isset( $instance['title'] ) ? $instance['title'] : '';
    
    echo '<p><label for="' . esc_attr( $this->get_field_name( 'title' ) ) . '">' . esc_html__( 'Title:' ) . '</label>';
		echo '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" /></p>';
	}

  /**
   * Facebook Page Widget template.
   *
   * @param array $args
   * @param array $instance
   */
  public function widget( $args, $instance ) {
    $options  = get_option( 'spfs_settings' );
    $page_url = isset( $options['social_network']['facebook'] ) ? $options['social_network']['facebook'] : 'https://www.facebook.com/facebook';
    
    echo $args['before_widget'];
		echo $args['before_title'];
		echo $instance['title'];
		echo $args['after_title'];
		
    $this->include_sdk(); ?>
    
    <div class="fb-page" data-href="<?php echo esc_url( $page_url ); ?>" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
			<div class="fb-xfbml-parse-ignore">
        <blockquote cite="<?php echo esc_url( $page_url ); ?>">
          <a href="<?php echo esc_url( $page_url ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
				</blockquote>
			</div>
		</div><?php
		
		echo $args['after_widget'];
	}

  /**
   * Facebook Page Widget SDK.
   */
	public function include_sdk() { ?>
    <div id="fb-root"></div>
		<script>
			( function( d, s, id ) {
				var js, fjs = d.getElementsByTagName( s )[0];
				if ( d.getElementById( id ) ) return;
				js = d.createElement( s ); js.id = id;
				js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.5";
				fjs.parentNode.insertBefore( js, fjs );
			}
			( document, 'script', 'facebook-jssdk' ) );
		</script><?php
  }
}
