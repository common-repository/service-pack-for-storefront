<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Newsletter Subscription Widget.
 *
 * @class    SPFS_Widget_Newsletter
 * @since    0.0.1
 * @package  SPFS/Widgets
 * @category Widgets
 * @author   Opportus
 */
class SPFS_Widget_Newsletter extends WP_Widget {

  /**
   * Newsletter Widget constructor.
   */  
  public function __construct() {
    parent::__construct( 'spfs_widget_newsletter', 'Newsletter', array( 'description' => __( 'Newsletter subscription widget.', 'service-pack-for-storefront' ) ) );
		add_action( 'wp_ajax_spfs_widget_newsletter_save_email', array( $this, 'save_email' ) );
		add_action( 'wp_ajax_nopriv_spfs_widget_newsletter_save_email', array( $this, 'save_email' ) );
	  add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) )	;
	}

  /**
   * Newsletter Widget form.
   *
   * @param array $instance
   */
	public function form( $instance ) {
    $title = isset( $instance['title'] ) ? $instance['title'] : '';
    
    echo '<p><label for="' . esc_attr( $this->get_field_name( 'title' ) ) . '">' . _e( 'Title:' ) . '</label>';
		echo '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" /></p>';
	}

  /**
   * Newsletter Widget template.
   *
   * @param array $args
   * @param array $instance
   */
	public function widget( $args, $instance ) {
    echo $args['before_widget'];
		echo $args['before_title'];
		echo $instance['title'];
		echo $args['after_title']; ?>
		
    <form class="spfs-widget-newsletter">
      <input name="spfs_widget_newsletter_email" placeholder="<?php esc_attr_e( 'Your email address', 'service-pack-for-storefront' ); ?>" type="email" class="spfs-widget-newsletter-email" />
      <input type="hidden" name="action" value="spfs_widget_newsletter_save_email" />
			<?php wp_nonce_field( 'spfs_widget_newsletter_nonce', 'security' ); ?>
			<button type="submit" class="spfs-widget-newsletter-send"></button>
		</form><?php
			
		echo $args['after_widget'];
  }

  /**
   * Validate, sanitize and save email address to the 'spfs_email_list' table.
   *
   * Hooked into 'wp_ajax_spfs_widget_newsletter_save_email' and 'wp_ajax_nopriv_spfs_widget_newsletter_save_email' actions.
   */
  public function save_email() {
    check_ajax_referer( 'spfs_widget_newsletter_nonce', 'security' );
    
    $errors = new WP_Error;
    // Response Messages...
    $response_message = array(
		  'too_long_email'    => __( 'The maximum length of you email address is 50 characters.', 'spfs' ),
		  'invalid_email'     => __( 'Invalid email address.', 'spfs' ),
		  'missing_email'     => __( 'Don\'t forget your email address...', 'spfs' ),
		  'already_subcribed' => __( 'You are already subcribed to our newsletter...', 'spfs' ),
		  'insertion_failure' => __( 'Sorry but you can not subscribe to our newsletter because something not predicted happened, please try again.', 'spfs' ),
		  'success'           => __( 'Thank you for subscribing to our newsletter !', 'spfs' )
    );
    apply_filters( 'spfs_newsletter_response_message', $response_message );

    if ( isset( $_POST['spfs_widget_newsletter_email'] ) && ! empty( $_POST['spfs_widget_newsletter_email'] ) ) {
			$email = $_POST['spfs_widget_newsletter_email'];
    
      // Validation & Sanitazation...
      if ( strlen( $email ) <= 50 ) {
      	if ( preg_match( '#^[a-z0-9_.-]+@[a-z0-9_.-]{2,}\.[a-z]{2,4}$#', $email ) ) {
          $valid_email = sanitize_email( $email );
					global $wpdb;
					$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}spfs_email_list WHERE email = '$valid_email'" );
          
          if ( is_null( $row ) ) {
            if ( ! $insertion_success = $wpdb->insert( "{$wpdb->prefix}spfs_email_list", array( 'email' => $valid_email, 'subscription' => current_time( 'mysql' ) ) ) ) {
					    $errors->add( 'insertion_failure', $response_message['insertion_failure'] );
						}
					} else {
						$errors->add( 'already_subscribed', $response_message['already_subcribed'] );
					}
				} else {
					$errors->add( 'invalid_email', $response_message['invalid_email'] );
				}
			} else {
				$errors->add( 'too_long_email', $response_message['too_long_email'] );
			}
		} else {
			$errors->add( 'missing_email', $response_message['missing_email'] );
    }

    // AJAX Response...
		$error_detected = $errors->get_error_code();
    
    if ( empty( $error_detected ) ) {
      wp_send_json_success( $response_message['success'] );
		}
		else if ( ! empty( $error_detected ) ) {
      $error = null;
      
      foreach ( $errors->get_error_messages() as $error_message ) {
        $error .= $error_message . ' ';
			}
			wp_send_json_error( esc_html( $error ) );
		}
		exit;
	}

  /**
   * Newsletter Widget scripts.
   *
   * Hooked into 'wp_enqueue_scripts' action.
   */
  public function enqueue_scripts() {
    wp_enqueue_style( 'spfs-widget-newsletter-style', SPFS_URL . 'assets/css/widget-newsletter.min.css' );
		wp_enqueue_script( 'spfs-widget-newsletter-script', SPFS_URL . 'assets/js/widget-newsletter.min.js', array( 'jquery' ) );
		wp_localize_script( 'spfs-widget-newsletter-script', 'spfs_widget_newsletter_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
  }
}
