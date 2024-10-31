<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Order Tracking module.
 *
 * @class    SPFS_Order_Tracking
 * @since    0.1.6
 * @package  SPFS/Modules
 * @category Modules
 * @author   Opportus
 */
class SPFS_Order_Tracking {

  /**
   * @var array $order_tracking_options Unserialized and restructured options.
   * @see SPFS_Order_Tracking::init_order_tracking_options
   */
  private $order_tracking_options;

  /**
   * @var string $tracking_shipper
   * @see SPFS_Order_Tracking::init_tracking_meta
   */
  private $tracking_shipper;

  /**
   * @var string $tracking_number
   * @see SPFS_Order_Tracking::init_tracking_meta
   */
  private $tracking_number;

  /**
   * Order Tracking module constructor.
   */
  public function __construct() {
    $this->init_order_tracking_options();
    add_filter( 'is_protected_meta', array( $this, 'hide' ), 10, 2 );
    add_action( 'save_post', array( $this, 'save' ) );
    add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'woocommerce_email_order_meta', array( $this, 'email_template' ) );
		add_action( 'woocommerce_before_my_account', array( $this, 'frontend_template' ), 20 );
  }

  /**
   * Initialize order tracking options.
   *
   * Grep serialized 'order_tracking' options from wp_options table and return them in a nicely structured array.
   * Pretty complicated for what it has to do... I believe there are better implementations or approches.
   * If you have a better idea, please, let me know !
   *
   * The option we're grepping are structured this way:
   * 
   * 'spfs_settings' = array(
   *   'order_tracking' => array(
   *     'shipper_name_0' => 'Foobar Shipper',
   *     'shipper_url_0'  => 'http://www.foobar.com/tracking-service?url=',
   *     'shipper_name_1' => null,
   *     'shipper_url_1'  => null,
   *   )
   * )
   * NOTE: The pair of option fields are added dynamically when a new shipper is found.
   * The '_0' or '_1' part is the incremental ID of the pair of fields.
   * For more info, check SPFS_Settings.
   *
   * We're attempting to return the options this way:
   *
   * 'foobar_shipper' = array(
   *   'name'         => 'Foobar Shipper',
   *   'url'          => 'http://www.foobar.com/tracking-service?url='
   * )
   *
   * @return array SPFS_Order_Tracking::$order_tracking_options
   * @see    SPFS_Settings
   */
   private function init_order_tracking_options() {
    $options = get_option( 'spfs_settings' );
    $shippers_options =  isset( $options['order_tracking'] ) ? $options['order_tracking'] : null;
    $shippers_tmp = array(); // Temporary array which will get filled by shippers classified by their ID.
    $shippers = array(); // Futur shippers array which will have their name (if available) as key. Else by thier ID.
    $number = 0;

    if ( is_null( $shippers_options ) ) {
      return;
    }
    foreach ( $shippers_options as $key => $value ) {
      list( , $option, $id ) = explode( '_', $key );
      $shippers_tmp[ $id ][ $option ] = isset( $value ) ? $value : null;
      
      if ( 'name' === $option ) {
        // If shipper's name has not been set by admin, set his ID as key.
        if ( is_null( $value ) ) {
          $new_key = $id;
        }
        // Else, set his name value as key.
        else {
          $new_key = str_replace( ' ', '_', strtolower( $value ) );
          // We have a shipper.
          $any_shipper = true;
        }
        $shippers[ $new_key ] = null;
      }
    }
    // Check if we've got a shipper.
    if ( isset( $any_shipper ) ) {
      // Change shippers keys by their new ID.
      foreach ( $shippers as $key => $value ) {
        $shippers[ $key ] = $shippers_tmp[ $number ];
        $number ++;
      }
      $this->order_tracking_options = $shippers;
    }
  }

  /**
   * Initialize order tracking post meta.
   *
   * @param mixed null|int $post_ID (default: null)
   * @see   SPFS_Order_Tracking::$tracking_shipper
   * @see   SPFS_Order_Tracking::$tracking_number
   */
  private function init_tracking_meta( $post_ID = null ) {
    if ( is_null( $post_ID ) ) {
      global $post;
      $post_ID = $post->ID;
    }
    $tracking_shipper = ! empty( $post_meta = get_post_meta( $post_ID, 'spfs_order_tracking_shipper', true ) ) ? $post_meta : null;
    $tracking_number  = ! empty( $post_meta = get_post_meta( $post_ID, 'spfs_order_tracking_number', true ) ) ? $post_meta : null;

    if ( ! is_null( $tracking_shipper ) && ! array_key_exists( $tracking_shipper, $this->order_tracking_options ) ) {
      delete_post_meta( $post_ID, 'spfs_order_tracking_shipper', $tracking_shipper );
      delete_post_meta( $post_ID, 'spfs_order_tracking_number', $tracking_number );
    }
    $this->tracking_shipper = ! empty( $post_meta = get_post_meta( $post_ID, 'spfs_order_tracking_shipper', true ) ) ? $post_meta : null;
    $this->tracking_number  = ! empty( $post_meta = get_post_meta( $post_ID, 'spfs_order_tracking_number', true ) ) ? $post_meta : null;
  }

  /**
   * Add meta box.
   *
   * Hooked into 'add_meta_boxes' action.
   */
  public function add_meta_boxes() {
    add_meta_box(
      'spfs_order_tracking',
      __( 'Order Tracking', 'service-pack-for-storefront' ),
      array( $this, 'meta_box' ),
      'shop_order',
      'side',
      'default'
    );
	}

  /**
   * Meta box template. 
   */
  public function meta_box() {
    if ( ! isset( $this->order_tracking_options ) ) {
      echo '<p>' . sprintf( __( 'You first need to add new shippers on the %s.', 'service-pack-for-storefront' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=spfs_settings_page' ) ) . '" target="_blank">' . __( 'settings page', 'service-pack-for-storefront' ) . '</a>' ) . '</p>';
      
      return;
    }
    $this->init_tracking_meta();

    echo '<p class="description">' . esc_html__( 'Select the shipper, enter your tracking number and save...', 'service-pack-for-storefront' ) . '</p>';
    echo '<p><label for="spfs_order_tracking_shipper">' . esc_html__( 'Shipper', 'service-pack-for-storefront' ) . '</label /><br />';
    echo '<select id="spfs_order_tracking_shipper" name="spfs_order_tracking_shipper">';
    echo '<option value="">' . esc_html__( 'Select the shipper', 'service-pack-for-storefront' ) . '</option>';
    
    foreach ( $this->order_tracking_options as $key => $value ) {
      if ( ! is_int( $key ) ) {
        $selected = ( $this->tracking_shipper === $key ) ? 'selected ' : '';

        echo '<option ' . $selected . 'value="' . esc_attr( $key ) . '">' . esc_html( $value['name'] ) . '</option>';
      }
    }
    echo '</select></p>';
    echo '<p><label for="spfs_order_tracking_number">' . esc_html__( 'Tracking number', 'service-pack-for-storefront' ) . '</label>';
    echo '<input type="text" id="spfs_order_tracking_number" name="spfs_order_tracking_number" value="' . esc_attr( $this->tracking_number ) . '" /></p>';
    
    if ( isset( $this->tracking_number ) && isset( $this->tracking_shipper ) ) {
      echo '<a href="' . esc_url( $this->order_tracking_options[ $this->tracking_shipper ]['url'] . $this->tracking_number ) . '" rel="nofollow" target="_blank">' . esc_html__( 'Track it', 'service-pack-for-storefront' ) . '</a>';
    }
  }

  /**
   * Save order tracking meta.
   *
   * Hooked into 'save_post' action.
   *
   * @param int $post_ID
   */
  public function save( $post_ID ) {
    if ( empty( $_POST['spfs_order_tracking_shipper'] ) || empty( $_POST['spfs_order_tracking_number'] ) || ! current_user_can( 'edit_post', $post_ID ) ) {
      return;
    }
    $track_shipper = sanitize_text_field( $_POST['spfs_order_tracking_shipper'] );
    $track_number = sanitize_text_field( $_POST['spfs_order_tracking_number'] );
    
    if ( preg_match( '/^[\p{L}0-9\s\-_]{2,50}$/u', $track_shipper ) && preg_match( '/^[\p{L}0-9\s\-_]{2,50}$/u', $track_number ) ) {
      update_post_meta( $post_ID, 'spfs_order_tracking_shipper', $track_shipper );
      update_post_meta( $post_ID, 'spfs_order_tracking_number', $track_number );
    }
    else set_transient( 'spfs_order_tracking_error', true, 60 );
  }

  /**
   * Admin notice template
   *
   * Hooked into 'admin_notices' action.
   */
  public function admin_notice() {
    if ( get_transient( 'spfs_order_tracking_error' ) ) {
      echo '<div class="updated error notice is-dismissible">';
		  echo '<p>' . esc_html__( 'Invalid / Missing tracking number or tracking shipper', 'service-pack-for-storefront' ) . '.</p>';
      echo '</div>';
      delete_transient( 'spfs_order_tracking_error' );
    }
	}

  /**
   * Email template.
   *
   * Hooked into 'woocommerce_email_order_meta' action.
   */
  public function email_template() {
    $this->init_tracking_meta();
    
    if ( ! isset( $this->tracking_shipper ) || ! isset( $this->tracking_number ) ) return;
    $html  = '<h3>' . esc_html__( 'Tracking information', 'service-pack-for-storefront' ) . '</h3>';
    $html .= '<p>' . esc_html__( 'You can track anytime your order by clicking', 'service-pack-for-storefront' ) . ' <a href="' . esc_url( $this->order_tracking_options[$this->tracking_shipper]['url'] . $this->tracking_number ) . '" target="_blank">' . esc_html__( 'here' ) . '</a>.</p>';
    
    echo apply_filters( 'spfs_order_tracking_email_template', $html );
	}	

  /**
   * Frontend template.
   *
   * Hooked into 'woocommerce_before_my_account' action.
   *
   */
  public function frontend_template() {
    $customer_post_orders = $this->get_customer_post_orders();
    
    if ( ! isset( $customer_post_orders ) ) {
      return;
    }
    ob_start();
    
    foreach ( $customer_post_orders as $post_order ) {
      $order = new WC_Order( $post_order->ID );
    
      $this->init_tracking_meta( $post_order->ID );
      
      if ( isset( $this->tracking_shipper ) && isset( $this->tracking_number ) ) {
        $loop  = '<li>';
			  $loop .= '<strong>' . sprintf( esc_html__( 'Order N° %s', 'service-pack-for-storefront' ), $number = esc_html( $order->get_order_number() ) ) . '</strong><br />';
			  $loop .= sprintf( esc_html__( 'Sent by %s', 'service-pack-for-storefront' ), $shipper = esc_html( $this->order_tracking_options[ $this->tracking_shipper ]['name'] ) ) . '<br />';
        $loop .= sprintf( esc_html__( 'Tracking number: %s', 'service-pack-for-storefront' ), $tracking = esc_html( $this->tracking_number ) ) . '<br />';
		    $loop .= sprintf( esc_html__( 'You can track anytime you order by clicking %s', 'service-pack-for-storefront' ), $link = '<a href="' . esc_url( $this->order_tracking_options[ $this->tracking_shipper ]['url'] . $this->tracking_number ) . '" rel="nofollow" target="_blank">' . esc_html__( 'here', 'service-pack-for-storefront' ) . '</a>.' );
        $loop .= '</li>';

        echo apply_filters( 'spfs_order_tracking_frontend_loop', $loop );
		  }
    }
    $orders = ob_get_clean();
    
    if ( ! $orders ) {
      return;
    }
    echo '<h2>' . esc_html__( 'My trackings', 'service-pack-for-storefront' ) . '</h2>';
    echo '<ul>';
    echo $orders;
    echo '</ul>';
	}
  	
  /**
   * Get the customer orders.
   *
   * @return array The customer orders.
   */
  private function get_customer_post_orders() {
		$user = wp_get_current_user();
		$args = array(
			'post_type'      => 'shop_order',
			'post_status'    => 'completed',
			'posts_per_page' => -1,
			'meta_value'   => $user->user_email,
		);
		return get_posts( $args );
  }

  /**
   * Hide 'spfs_order_tracking_*' custom fields.
   *
   * Hooked into 'is_protected_meta' action.
   *
   * @param  bool $protected
   * @param  int $meta_key
   * @return bool
   */
  public function hide( $protected, $meta_key ) {
    if ( 'spfs_order_tracking_shipper' === $meta_key || 'spfs_order_tracking_number' === $meta_key ) {
      return true;
		}
		return $protected;
  }
}
