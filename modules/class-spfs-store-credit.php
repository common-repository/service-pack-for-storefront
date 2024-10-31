<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Store Credit Module.
 *
 * Add 'spfs_store_credit' discount type.
 * Send store credits to customer by email.
 * Display store credits on WooCommerce 'My Account' page.
 *
 * @class    SPFS_Store_Credit
 * @since    0.0.1
 * @package  SPFS/Modules
 * @category Modules
 * @author   Opportus
 */
class SPFS_Store_Credit {

  /**
   * @var array $store_credit_options
   * @see SPFS_Store_Credit::init_store_credit_options()
   */
  private $store_credit_options;

  /**
   * Store Credit Module constructor.
   */
  public function __construct() {
    $this->init_store_credit_options();
    add_filter( 'woocommerce_coupon_discount_types', array( $this, 'init_discount_type' ) );
    add_action( 'admin_menu', array( $this, 'init_submenu_page_store_credit_send' ), 10 );
    add_action( 'load-woocommerce_page_spfs_store_credit_send', array( $this, 'handler_store_credit_send' ) );
    add_action( 'admin_notices', array( $this, 'admin_notice_store_credit_send' ) );
		add_action( 'woocommerce_before_my_account', array( $this, 'store_credit_my_account_template' ), 20 );
    add_filter( 'woocommerce_coupon_is_valid', array( $this, 'coupon_is_valid' ), 10, 2 );
		add_filter( 'woocommerce_coupon_is_valid_for_cart', array( $this, 'coupon_is_valid_for_cart' ), 10, 2 );
		add_action( 'woocommerce_new_order', array( $this, 'update_credit_amount' ), 9 );
		add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'coupon_get_discount_amount' ), 10, 5 );
		add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'cart_totals_coupon_label' ), 10, 2 );
  }

  /**
   * Fetch Store Credit options.
   */
  private function init_store_credit_options() {
    $options = get_option( 'spfs_settings' );
    
    $this->store_credit_options = $options['store_credit'];
  }

  /**
   * Add 'spfs_store_credit' discount type.
   *
   * Hooked into 'woocommerce_coupon_discount_types' action.
   *
   * @param  array $discount_types
   * @return array $discount_types
   */
  public function init_discount_type( $discount_types ) {
    $discount_types['spfs_store_credit'] = __( 'Store Credit', 'service-pack-for-storefront' );
    
    return $discount_types;
  }

  /**
   * Initialize Store Credit admin submenu page from which we send store credits by email.
   *
   * Hooked into 'admin_menu' action.
   */
  public function init_submenu_page_store_credit_send() {
    add_submenu_page(
      'woocommerce',
      __( 'Send Credits', 'service-pack-for-storefront' ),
      __( 'Send Credits', 'service-pack-for-storefront' ),
      'manage_woocommerce',
      'spfs_store_credit_send',
      array( $this, 'submenu_page_store_credit_send_template' )
    );
	}

  /**
   * Submenu page template.
   */
  public function submenu_page_store_credit_send_template() {
    echo '<div class="wrap">';
		echo '<div id="icon-woocommerce" class="icon32 icon32-posts-shop_coupon"><br></div>';
		echo '<h2>' . esc_html__( 'Send Credits', 'service-pack-for-storefront' ) . '</h2>';
		echo '<form method="post">';
		echo '<table class="form-table">';
		echo '<tr valign="top">';
		echo '<th scope="row">' . esc_html__( 'Email address', 'service-pack-for-storefront' ) . '</th>';
	  echo '<td><input id="spfs_store_credit_send_email" name="spfs_store_credit_send_email" class="regular-text" /></td>';
		echo '</tr>';
		echo '<tr valign="top">';
		echo '<th scope="row">' . esc_html__( 'Amount of the credit', 'service-pack-for-storefront' ) . '</th>';
	  echo '<td><input id="spfs_store_credit_send_amount" name="spfs_store_credit_send_amount" class="regular-text" placeholder="0.00" /></td>';
		echo '</tr>';
    echo '</table>';

    submit_button( esc_html__( 'Generate a store credit and mail it to your customer', 'service-pack-for-storefront' ) );
    wp_nonce_field( 'spfs_store_credit_send_nonce', 'security' );
    
    echo '</div>';
	}

  /**
   * Store Credit send handling.
   *
   * Update post meta and send email.
   *
   * Hooked into 'load-woocommerce_page_spfs_store_credit_send' action.
   */
  public function handler_store_credit_send() {
    if ( ! isset( $_POST['spfs_store_credit_send_email'] ) || wp_verify_nonce( 'security', 'spfs_store_credit_send_nonce' ) ) {
      return;
    }
    $email  = sanitize_email( $_POST[ 'spfs_store_credit_send_email'] );
		$amount = sanitize_text_field( $_POST['spfs_store_credit_send_amount'] );
    
    if ( ! is_email( $email ) ) {
      $error_message = esc_html__( 'Invalid email address', 'service-pack-for-storefront' );
    }
    if ( ! is_numeric( $amount ) || 1 > $amount ) {
      $error_message = isset( $error_message ) ? $error_message . '<br />' : '';
      $error_message .= esc_html__( 'Invalid amount', 'service-pack-for-storefront' );
    }
    if ( isset( $error_message ) ) {
      set_transient( 'spfs_store_credit_send_error', $error_message, 60 );
      return;
    }
    $coupon_code = uniqid( sanitize_email( $email ) . '-' );
    
    apply_filters( 'spfs_store_credit_coupon_code', $coupon_code, $email );
    
    $coupon_id = wp_insert_post(
      array(
			  'post_title'   => $coupon_code,
			  'post_content' => '',
			  'post_status'  => 'publish',
			  'post_author'  => 1,
			  'post_type'    => 'shop_coupon'
      )
    );
		update_post_meta( $coupon_id, 'discount_type', 'spfs_store_credit' );
		update_post_meta( $coupon_id, 'coupon_amount', $amount );
		update_post_meta( $coupon_id, 'individual_use', $this->store_credit_options['individual_use'] );
		update_post_meta( $coupon_id, 'product_ids', '' );
		update_post_meta( $coupon_id, 'exclude_product_ids', '' );
		update_post_meta( $coupon_id, 'usage_limit', '' );
		update_post_meta( $coupon_id, 'expiry_date', '' );
		update_post_meta( $coupon_id, 'apply_before_tax', $this->store_credit_options['before_tax'] );
		update_post_meta( $coupon_id, 'free_shipping', 'no' );
		update_post_meta( $coupon_id, 'customer_email', array( $email ) );
    
    $this->email_store_credit_send( $email, $coupon_code, $amount );
    
    set_transient( 'spfs_store_credit_send_success', __( 'Credit sent.', 'service-pack-for-storefront' ), 60 );
  }

  /**
   * Admin notice template.
   *
   * Hooked into 'admin_notices' action.
   */
  public function admin_notice_store_credit_send() {
    if ( $success_message = get_transient( 'spfs_store_credit_send_success' ) ) {
      echo '<div id="message" class="updated fade"><p><strong>' . $success_message . '</strong></p></div>';
      
      delete_transient( 'spfs_store_credit_send_success' );
      return;
    }
    if ( $error_message = get_transient( 'spfs_store_credit_send_error' ) ) {
      echo '<div id="message" class="error fade"><p><strong>' . $error_message . '</strong></p></div>';
      
      delete_transient( 'spfs_store_credit_send_error' );
    }
  }

  /**
   * Store Credit Module mailer.
   *
   * @param string $email Recipient email.
   * @param string $coupon_code
   * @param mixed int|float $amount
   */
  private function email_store_credit_send( $email, $coupon_code, $amount ) {
    $mailer        = WC()->mailer();
		$blogname      = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$subject       = sprintf( '[%s] %s', $blogname, __( 'Store Credit', 'service-pack-for-storefront' ) );
		$email_heading = sprintf( __( 'You have got a credit of %s ', 'service-pack-for-storefront' ), woocommerce_price( $amount ) );
    
    ob_start();
		$this->send_store_credit_email_template( $email_heading, $coupon_code );
	  $message = ob_get_clean();
    
    $mailer->send( $email, $subject, $message );
  }

  /**
   * Store Credit Module email template.
   *
   * @param string $email_heading
   * @param string $coupon_code
   */
  private function send_store_credit_email_template( $email_heading, $coupon_code ) {
    do_action( 'woocommerce_email_header', $email_heading );

    $html  = '<p>' . esc_html__( 'To use your store credit, please, enter the following discount code during your next checkout', 'service-pack-for-storefront' ) . '.</p>';
    $html .= '<strong style="margin: 10px 0; font-size: 18px; font-weight: bold; display: block; text-align: center;">' . esc_html( $coupon_code ) . '</strong>';
    $html .='<div style="clear:both;"></div>';
    
    apply_filters( 'spfs_store_credit_email_html', $html, $coupon_code );
    echo $html;
    
    do_action( 'woocommerce_email_footer' );
  }

  /**
   * Store Credit Module frontend template.
   *
   * Hooked into 'woocommerce_before_my_account'.
   */
	public function store_credit_my_account_template() {
    if ( $coupons = $this->get_customer_credit() ) {
      $coupon_count = 0;
      
      ob_start();
      
      echo '<h2>' . esc_html__( 'My Store Credits', 'service-pack-for-storefront' ) . '</h2>';
			echo '<ul class="spfs-store-credit">';
      
      foreach ( $coupons as $code ) {
				$coupon = new WC_Coupon( $code->post_title );
				if ( 'spfs_store_credit' === $coupon->type || $coupon->is_store_credit ) {
          echo '<li><strong>' . $coupon->code . '</strong> &mdash;' . wc_price( $coupon->amount ) . '</li>';
          $coupon_count++;
				}
			}
      echo '</ul>';
      
      $html = ob_get_clean();
      apply_filters( 'spfs_store_credit_my_account', $html, $coupons );
      
      if ( $coupon_count ) {
        echo $html;
      }
		}
	}

  /**
   * Get customer store credits.
   *
   * @return array Posts
   */
  private function get_customer_credit() {
    if ( ! $this->store_credit_options['my_account'] ) {
      return false;
    }
		$user = wp_get_current_user();
		$args = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'customer_email',
					'value'   => $user->user_email,
					'compare' => 'LIKE'
				),
				array(
					'key'     => 'coupon_amount',
					'value'   => '0',
					'compare' => '>=',
					'type'    => 'NUMERIC'
				)
			)
		);
		return get_posts( $args );
	}

  /**
   * Coupon validation check.
   *
   * Hooked into 'woocommerce_coupon_is_valid' filter.
   *
   * @param  bool $valid
   * @param  object $coupon
   * @return bool
   */
	public function coupon_is_valid( $valid, $coupon ) {
    if ( $valid && ( 'spfs_store_credit' === $coupon->type || $coupon->is_store_credit ) && $coupon->amount <= 0 ) {
			wc_add_notice( esc_html__( 'Your credit is over.', 'service-pack-for-storefront' ), 'error' );
			return false;
		}
		return $valid;
	}

  /**
   * Coupon cart validation check.
   *
   * Hooked into 'woocommerce_coupon_is_valid_for_cart' filter.
   *
   * @param  bool $valid
   * @param  object $coupon
   * @return bool
   */
  public function coupon_is_valid_for_cart( $valid, $coupon ) {
    if ( ( 'spfs_store_credit' === $coupon->type || $coupon->is_store_credit ) ) {
			return true;
		}
		return $valid;
	}

  /**
   * Update credit amount.
   *
   * Hooked into 'woocommerce_new_order' action.
   */
	public function update_credit_amount() {
    if ( $coupons = WC()->cart->get_coupons() ) {
			foreach ( $coupons as $code => $coupon ) {
				if ( ( 'spfs_store_credit' === $coupon->type || $coupon->is_store_credit ) ) {
					$credit_remaining = max( 0, ( $coupon->amount - WC()->cart->coupon_discount_amounts[ $code ] ) );
          
          if ( $credit_remaining <= 0 && $this->store_credit_options['after_usage'] ) {
						wp_delete_post( $coupon->id );
					}
					else {
						update_post_meta( $coupon->id, 'coupon_amount', $credit_remaining );
					}
				}
			}
		}
	}

  /**
   * Get coupon discount amount.
   *
   * Hooked into 'woocommerce_coupon_get_discount_amount' filter.
   */
  public function coupon_get_discount_amount( $discount, $discounting_amount, $cart_item, $single, $coupon ) {
    if ( ( 'spfs_store_credit' === $coupon->type || $coupon->is_store_credit ) && ! is_null( $cart_item ) ) {
			$discount_percent = 0;
			if ( WC()->cart->subtotal_ex_tax ) {
				$discount_percent = ( $cart_item['data']->get_price_excluding_tax() * $cart_item['quantity'] ) / WC()->cart->subtotal_ex_tax;
			}
			$discount = min( ( $coupon->amount * $discount_percent ) / $cart_item['quantity'], $discounting_amount );
		}
		elseif ( ( 'spfs_store_credit' === $coupon->type || $coupon->is_store_credit ) ) {
			$discount = min( $coupon->amount, $discounting_amount );
		}
		return $discount;
	}

  /**
   * Cart totals coupon label.
   *
   * Hooked into 'woocommerce_cart_totals_coupon_label' filter.
   */
  public function cart_totals_coupon_label( $label, $coupon ) {
    if ( ( 'spfs_store_credit' === $coupon->type || $coupon->is_store_credit ) ) {
			$label = __( 'Store Credit', 'service-pack-for-storefront' );
		}
		return $label;
	}
}
