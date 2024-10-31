<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Contact Form Module.
 *
 * A simple and straightforward contact form that you can add anywhere with its shortcode.
 *
 * @class    SPFS_Contact_Form
 * @since    0.1.6
 * @package  SPFS/Modules
 * @category Modules
 * @author   Opportus
 * @todo     Implement email attachment...
 */
class SPFS_Contact_Form {

  /**
   * Contact Form Module constructor.
   */
  public function __construct() {
    add_shortcode( 'spfs_contact_form', array( $this, 'shortcode' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    add_action( 'wp_ajax_spfs_contact_form_handler', array( $this, 'handler' ) );
    add_action( 'wp_ajax_nopriv_spfs_contact_form_handler', array( $this, 'handler' ) );
  }

  /**
   * Contact Form Module scripts.
   *
   * Hooked into 'wp_enqueue_scripts' action.
   */
  public function enqueue_scripts() {
    if ( SPFS::get_instance()->page_has_shortcode( 'spfs_contact_form' ) ) {
      wp_register_style( 'spfs-contact-form-style', SPFS_URL . 'assets/css/contact-form.min.css' );
	    wp_register_script( 'spfs-contact-form-script', SPFS_URL . 'assets/js/contact-form.min.js', array( 'jquery' ) );
	    wp_enqueue_style( 'spfs-contact-form-style' );
		  wp_enqueue_script( 'spfs-contact-form-script' );
      wp_localize_script( 'spfs-contact-form-script', 'spfs_contact_form_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
    }
  }

  /**
   * Contact Form Module shortcode.
   *
   * Hooked into 'spfs_contact_form' action.
   *
   * @return string $contact_form HTML template.
   */
  public function shortcode() {
    ob_start();
	  $this->template();
	  $contact_form = ob_get_clean();
    
    return $contact_form;
  }

  /**
   * Contact Form Module template.
   */
  private function template() { 
    do_action( 'spfs_contact_form_before' ); ?>
    
    <form id="spfs-contact-form">
      
      <div class="spfs-contact-form-panel-left">
        <label for="spfs-contact-form-email"><?php esc_html_e( 'Email', 'service-pack-for-storefront' ); ?><abbr class="required" title="<?php esc_attr_e( 'Required', 'service-pack-for-storefront' ); ?>"> *</abbr></label>
			  <input type="email" id="spfs-contact-form-email" name="spfs_contact_form_email" value="<?php if ( isset( $_POST['spfs_contact_form_email'] ) ) echo  esc_attr( $_POST['spfs_contact_form_email'] ); ?>" required />
        <label for="spfs-contact-form-name"><?php esc_html_e( 'Name', 'service-pack-for-storefront' ); ?></label>
			  <input type="text" id="spfs-contact-form-name" name="spfs_contact_form_name" value="<?php if ( isset( $_POST['spfs_contact_form_name'] ) ) echo esc_attr( $_POST['spfs_contact_form_name'] ); ?>" />
      </div>

      <div class="spfs-contact-form-panel-rigth">
        <label for="spfs-contact-form-message"><?php esc_html_e( 'Message', 'service-pack-for-storefront' ); ?><abbr class="required" title="<?php esc_attr_e( 'Required', 'service-pack-for-storefront' ); ?>"> *</abbr></label>
			  <textarea id="spfs-contact-form-message" name="spfs_contact_form_message" rows="17" required><?php if ( isset( $_POST['spfs_contact_form_message'] ) ) echo esc_textarea( $_POST['spfs_contact_form_message'] ); ?></textarea>
      </div>
      
			<?php wp_nonce_field( 'spfs_contact_form_nonce', 'security' ); ?>

      <input type="hidden" name="action" value="spfs_contact_form_handler">
			<button type="submit" id="spfs-contact-form-send"></button>
    </form><?php

    do_action( 'spfs_contact_form_after' );
  }

  /**
   * Contact Form Module handler.
   *
   * Validate and sanitize email compenents, then send the email with AJAX.
   * 
   * Hooked into 'wp_ajax_spfs_contact_form_handler' and 'wp_ajax_nopriv_spfs_contact_form_handler' actions.
   */
  public function handler() {
	  check_ajax_referer( 'spfs_contact_form_nonce', 'security' );
    
    $errors           = new WP_Error();
	  $email            = $_POST['spfs_contact_form_email'];
	  $message          = $_POST['spfs_contact_form_message'];
	  $name             = $_POST['spfs_contact_form_name'];
	  $valid_email      = null;
	  $valid_message    = null;
	  $valid_name       = '?';
    $response_message = array(
	    'too_long_email'    => __( 'The max length for your email address is 30 characters.', 'service-pack-for-storefront' ),
	    'too_long_message'  => __( 'The max length for your message is 2000 characters.', 'service-pack-for-storefront'),
	    'too_long_name'     => __( 'The max length for your name is 30 characters.', 'service-pack-for-storefront' ),
      'invalid_email'     => __( 'Invalid email.', 'service-pack-for-storefront' ),
	    'invalid_message'   => __( 'Invalid message.', 'service-pack-for-storefront' ),
	    'invalid_name'      => __( 'Invalid name.', 'service-pack-for-storefront' ),
      'missing_email'     => __( 'Don\'t forget your email address...', 'service-pack-for-storefront' ),
	    'missing_message'   => __( 'Don\'t forget your message...', 'service-pack-for-storefront' ),
      'failure'           => __( 'Your message has not been sent. Please, try again...', 'service-pack-for-storefront' ),
      'success'           => __( 'Your message has successfully been sent. We will reply to you as soon as possible.', 'service-pack-for-storefront' )
    );
    apply_filters( 'spfs_contact_form_response_message', $response_message );
    
    // Email Validation & Sanitization...
    if ( ! empty( $email) ) {
      if ( strlen( $email ) <= 30 ) {
        if ( preg_match( '/^[a-z0-9_.-]+@[a-z0-9_.-]{2,}\.[a-z]{2,4}$/', $email ) ) {
          $valid_email = sanitize_email( $email );
        }
        else {
          $errors->add( 'invalid_email', $response_message['invalid_email'] );
			  }
      }
      else {
        $errors->add( 'too_long_email', $response_message['too_long_email'] );
		  }
    }
    else {
      $errors->add( 'missing_email', $response_message['missing_email'] );
	  }
    
    // Message Validation & Sanitization...
	  if ( ! empty( $message ) ) {
      if ( strlen( $message ) <= 2000 ) {
        if ( preg_match( '/^.+$/s', $message ) ) {
          $valid_message = wp_kses( nl2br( $message ), array( 'br' => array() ) );
        }
        else {
          $errors->add( 'invalid_message', $response_message['invalid_message'] );
			  }
      }
      else {
        $errors->add( 'too_long_message', $response_message['too_long_message'] );
		  }
    }
    else {
      $errors->add( 'missing_message', $response_message['missing_message'] );
    }

    // Name Validation & Sanitization...
	  if ( ! empty( $name ) ) {  
      if ( strlen( $name ) <= 60 ) {
        if ( preg_match( '/^[\p{L}\s]+$/', $name ) ) {
          $valid_name = sanitize_text_field( $name );
				  $valid_message .= '<p>' . esc_html__( 'Name', 'service-pack-for-storefront' )  . ': ' . esc_html( $valid_name ) . '</p>';
			  } else {
          $errors->add( 'invalid_name', $response_message['invalid_name'] );
			  }
		  } else {
        $errors->add( 'too_long_name', $response_message['too_long_name'] );
		  }
	  }
    $error_detected = $errors->get_error_code();

	  // Mailer...
    if ( empty( $error_detected ) && ! empty( $valid_email ) && ! empty( $valid_message ) ) {
		  $to      = get_option( 'admin_email' );
      $subject = esc_html__( 'You have got an email from', 'service-pack-for-storefront' ) . ' ' . $valid_name;
		  $headers = array( 'From: ' . $valid_email,
			  'Reply-To: ' . $valid_email,
			  'Content-Type: text/html; charset=UTF-8'
      );
      $email_compenents = array(
        'recipient' => $to,
        'subject'   => $subject,
        'message'   => $valid_message,
        'from'      => $valid_email
      );
      apply_filters( 'spfs_contact_form_email_compenents', $email_compenents );

      // Send Email...
		  if ( ! $is_sent = $this->mailer( $to, $subject, $valid_message, $headers ) ) {  
        $errors->add( 'failure', $response_message['failure'] );
      }
	  }
   
    // AJAX response...
	  $error_detected = $errors->get_error_code();
	  if ( empty( $error_detected ) ) {  
      wp_send_json_success( esc_html( $response_message['success'] ) );
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
   * Contact Form Module mailer.
   *
   * Send the mail with WooCommerce mailer if activated...
   * Else use WordPress mailer.
   * 
   * @param  string $to Recipient email address.
   * @param  string $subject The email subject.
   * @param  string $message The validate text message.
   * @param  string $headers The email headers.
   * @return bool $is_sent true if the email has successfully been sent or false if not.
   */
  private function mailer( $to, $subject, $message, $headers ) {
    if ( ! SPFS::get_instance()->is_missing_dependency( 'woocommerce' ) ) {
      $mailer = WC()->mailer();
      $is_sent = $mailer->send( $to, $subject, $this->email_template( $subject, $message ), $headers );
    }
    else {
      $is_sent = wp_mail( $to, $subject, $this->email_template( $subject, $message ), $headers );
    }
    return $is_sent;
  }

  /**
   * Contact Form Module email template.
   *
   * @param  string $subject The email subject.
   * @param  string $message The valid text message.
   * @return string $message The html or text message.
   */
  private function email_template( $subject, $message ) {
    $email_header_hook = ! SPFS::get_instance()->is_missing_dependency( 'woocommerce' ) ? 'woocommerce_email_header' : 'spfs_email_header';
    $email_footer_hook = ! SPFS::get_instance()->is_missing_dependency( 'woocommerce' ) ? 'woocommerce_email_footer' : 'spfs_email_footer';

    ob_start();
    do_action( $email_header_hook, $subject );
    echo $message;
    do_action( $email_footer_hook );
    $message = ob_get_clean();

    return $message;
  }
}
