<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Plugin's admin settings class.
 *
 * @class    SPFS_Settings
 * @since    0.0.1
 * @package  SPFS/Admin
 * @category Admin
 * @author   Opportus
 */
class SPFS_Settings {

  /**
   * @var odject $instance Singleton SPFS_Settings instance.
   */
  private static $instance;

  /**
   * @var array $settings_fields Store the settings fields.
   */
  private $settings_fields;

  /**
   * @var array $settings_sections Store the settings sections.
   */
  private $settings_sections;

  /**
   * Initialize singleton self instance.
   *
   * @return object SPFS_Settings::$instance
   */
  public static function get_instance() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new SPFS_Settings();
    }
    return self::$instance;
  }

  /**
   * Get settings fields.
   *
   * @return array SPFS::$settings_fields
   */
  public function get_settings_fields() {
    return $this->settings_fields;
  }

  /**
   * Get settings sections.
   *
   * @return array SPFS::$settings_sections
   */
  public function get_settings_sections() {
    return $this->settings_sections;
  }

  /**
   * Plugin's admin settings constructor.
   *
   * Initialize settings fields, sections and page.
   */
  private function __construct() {
    $this->set_settings_fields();
    $this->set_settings_sections();
    add_action( 'admin_menu', array( $this, 'init_options_page' ) );
    add_action( 'admin_init', array( $this, 'init_settings_sections' ) );
    add_action( 'admin_init', array( $this, 'init_settings_fields' ) );
  }

  /**
   * Set the settings fields.
   *
   * @param mixed false|array $additional_settings_fields (default: false)
   */
  private function set_settings_fields( $additional_settings_fields = false ) {
    $settings_fields = array(
      'aggregator'     => array(
        'name'         => __( 'Aggregator', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_modules_activation',
        'description'  => __( 'Aggregates the last blog posts on home page and product reviews in product category pages.', 'service-pack-for-storefront' ),
        'require'      => 'storefront'
      ),
      'contact_form'   => array(
        'name'         => __( 'Contact Form', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_modules_activation',
        'description'  => __( 'Simple front end contact form. Once activated, add its shortcode on your "contact" page: [spfs_contact_form]', 'service-pack-for-storefront' )
      ),
      'dynamic_sidebar'=> array(
        'name'         => __( 'Dynamic Sidebar', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_modules_activation',
        'description'  => __( 'Adds specific sidebar for product page, product category page, post page, etc...', 'service-pack-for-storefront' ),
        'require'      => 'storefront'
      ),
      'float_menu'     => array(
        'name'         => __( 'Float Menu', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_modules_activation',
        'description'  => __( 'Makes the basic storefront navigation menu floating when scrolling down.', 'service-pack-for-storefront' ),
        'require'      => 'storefront'
      ),
      'order_tracking' => array(
        'name'         => __( 'Order Tracking', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_modules_activation',
        'description'  => __( 'Gives you and your customers the ability to track simply orders via links pointing to the shipper\'s site tracking service.', 'service-pack-for-storefront' ),
        'require'      => 'woocommerce'
      ),
      'sharer'         => array(
        'name'         => __( 'Sharer', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_modules_activation',
        'description'  => __( 'Brings to your customers the possibility to share easily products and blog posts on their social network accounts.', 'service-pack-for-storefront' ),
        'require'      => 'storefront'
      ),
      'slider'         => array(
        'name'         => __( 'Slider', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_modules_activation',
        'description'  => __( 'Simple slider based on "Flex Slider" by WooThemes. Edit slides in the new menu section.', 'service-pack-for-storefront' )
      ),
      'store_credit'   => array(
        'name'         => __( 'Store Credit', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_modules_activation',
        'description'  => __( 'Gives you the ability to create and send by email store credits to your customers.', 'service-pack-for-storefront' ),
        'require'      => 'woocommerce'
      ),
      'facebook'       => array(
        'name'         => 'Facebook',
        'type'         => 'text',
        'section'      => 'spfs_settings_social_network'
      ),
      'twitter'        => array(
        'name'         => 'Twitter',
        'type'         => 'text',
        'section'      => 'spfs_settings_social_network'
      ),
      'googleplus'     => array(
        'name'         => 'Google +',
        'type'         => 'text',
        'section'      => 'spfs_settings_social_network'
      ),
      'instagram'      => array(
        'name'         => 'Instagram',
        'type'         => 'text',
        'section'      => 'spfs_settings_social_network'
      ),
      'youtube'        => array(
        'name'         => 'YouTube',
        'type'         => 'text',
        'section'      => 'spfs_settings_social_network'
      ),
      'my_account'     => array(
			  'name'         => __( 'My account', 'service-pack-for-storefront' ),
			  'type'         => 'checkbox',
        'section'      => 'spfs_settings_store_credit',
        'description'  => __( 'Show credits on My Account page.', 'service-pack-for-storefront' ),
        'modulable'    => 'store_credit'
		  ),
		  'after_use'      => array(
			  'name'         => __( 'Delete after use', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_store_credit',
        'description'  => __( 'When the credit is spent, delete it.', 'service-pack-for-storefront' ),
        'modulable'    => 'store_credit'
		  ),
		  'before_tax'	   => array(
			  'name'         => __( 'Apply before taxes', 'service-pack-for-storefront' ),
        'type'         => 'checkbox',
        'section'      => 'spfs_settings_store_credit',
			  'description'  => __( 'Apply the credit before taxes.', 'service-pack-for-storefront' ),
        'modulable'    => 'store_credit'
      ),
      'individual_use' => array(
        'name'         => __( 'Individual usage', 'service-pack-for-storefront' ),
			  'type'         => 'checkbox',
        'section'      => 'spfs_settings_store_credit',
        'modulable'    => 'store_credit'
      ),
      'shipper_name_0' => array(
        'name'         => __( 'Shipper\'s Name', 'service-pack-for-storefront' ),
        'type'         => 'text',
        'section'      => 'spfs_settings_order_tracking',
        'description'  => __( 'Enter the name of the new shipper. Note that it will be displayed as such to your customer.', 'service-pack-for-storefront' ),
        'modulable'    => 'order_tracking'
      ),
      'shipper_url_0'  => array(
        'name'         => __( 'Shipper\'s URL', 'service-pack-for-storefront' ),
        'type'         => 'text',
        'section'      => 'spfs_settings_order_tracking',
        'description'  => __( 'Add the new shipper\'s tracking service URL. Eg for FEDEX: http://www.fedex.com/Tracking?action=track&tracknumbers=<br />More examples <a href="http://verysimple.com/2011/07/06/ups-tracking-url/" rel="nofollow" target="_blank">here</a>.', 'service-pack-for-storefront' ),
        'modulable'    => 'order_tracking'
      )
    );
    if ( is_null( $this->settings_fields ) ) {
      $this->settings_fields = $settings_fields;
    }
    // If this method has been called with additional settings fields as argument...
    elseif ( $additional_settings_fields ) {
      // Merge settings fields.
      $this->settings_fields += $additional_settings_fields;
    }
  }

  /**
   * Set the settings sections.
   */
  private function set_settings_sections() {
    $settings_sections = array(
      'modules_activation' => array(
        'name'             => __( 'Modules Activation', 'service-pack-for-storefront' ),
        'description'      => __( 'Enable/Disable the modules below...', 'service-pack-for-storefront' )
      ),
      'social_network'     => array(
        'name'             => __( 'Social Network', 'service-pack-for-storefront' ),
        'description'      => __( 'Your social network pages URLs...', 'service-pack-for-storefront' )
      ),
      'store_credit'       => array(
        'name'             => __( 'Module - Store Credit', 'service-pack-for-storefront' ),
        'description'      => __( 'Store credit settings', 'service-pack-for-storefront' ),
        'modulable'        => 'store_credit'
      ),
      'order_tracking'     => array(
        'name'             => __( 'Module - Order Tracking', 'service-pack-for-storefront' ),
        'description'      => __( 'In this section, you can add new shippers and/or edit the shippers previously created.<br />Once the shippers created, go on the "Edit Order" page, in the new "Order Tracking" metabox on the right panel, select the shipper\'s name and enter your tracking number.<br />Now, your customers have the possibility to track their orders with a simple click from their account page or from their updated order status email they will get.', 'service-pack-for-storefront' ),
        'modulable'        => 'order_tracking'
      )
    );
    if ( is_null( $this->settings_sections ) ) {
      $this->settings_sections = $settings_sections;
    }
  }

  /**
   * Initialize plugin's options page.
   *
   * Hooked into 'admin_menu' action.
   */
  public function init_options_page() {
    add_options_page(
      __( 'Service Pack for Storefront', 'service-pack-for-storefront' ),
      __( 'Service Pack for Storefront', 'service-pack-for-storefront' ),
      'manage_options',
      'spfs_settings_page',
      array( $this, 'settings_page_template' )
    );
  }

  /**
   * Initialize option page's settings sections.
   *
   * Hooked into 'admin_init' action.
   */
  public function init_settings_sections() {
    // Get the options from wp_options database table.
    $options = get_option( 'spfs_settings' );

    foreach ( $this->settings_sections as $section => $setting ) {
      // Add the section if it doesn't concern a particular module OR if this module is activated.
      if ( ! isset( $setting['modulable'] ) || isset( $setting['modulable'] ) && isset( $options['modules_activation'][ $setting['modulable'] ] ) ) {
        add_settings_section(
          'spfs_settings' . '_' . $section,
          $setting['name'],
          array( $this, 'settings_sections_template' ),
          'spfs_settings_page'
        );
      }
    }
  }

  /**
   * Initialize options page's fields.
   *
   * Hooked into 'admin_init' action.
   */
  public function init_settings_fields() {
    register_setting(
      'spfs_settings_group',
      'spfs_settings',
      array( $this, 'sanitization' )
    );
    // Get the options from wp_options database table.
    $options = get_option( 'spfs_settings' );
    
    // Let's add dynamically new pair of settings fields for the 'order_tracking' settings section...
    if ( isset( $options['order_tracking'] ) ) {
      // Grep 'shipper_name' fields in options.
      $shippers = preg_grep( '#^shipper_name#', array_keys( $options['order_tracking'] ) );
      $shippers_number = 0;

      foreach( $shippers as $shipper ) {
        // If 'shipper_name' value is not null...
        if ( ! is_null( $options['order_tracking'][ $shipper ] ) ) {
          // Count one more shipper.
          $shippers_number ++;
        }
      }
      // If at least 1 shipper has been set, let's add new 'shipper_name' and 'shipper_url' setting fields pair...
      if ( $shippers_number ) {
        // We count the original shipper's setting fields pair which has the ID '_0'.
        // The $field_number variable will also define the new shipper's setting fields pair ID...
        $field_number = 1;

        while ( $field_number <= $shippers_number ) {
          // Create new shipper's setting fields pair with their new ID ($field_number)...
          $new_fields = array(
            'shipper_name_' . $field_number => $this->settings_fields['shipper_name_0'],
            'shipper_url_' . $field_number  => $this->settings_fields['shipper_url_0']
          );
          // Add them to the existing settings fields.
          $this->set_settings_fields( $new_fields );
          $field_number ++;
        }
      }
    }

    // Now, let's add the settings fields.
    foreach ( $this->settings_fields as $field => $setting ) {
      // Add the field if it doesn't concern a particular module OR if this module is activated.
      if ( ! isset( $setting['modulable'] ) || isset( $setting['modulable'] ) && isset( $options['modules_activation'][ $setting['modulable'] ] ) ) {
        // Remove section prefix 'spfs_settings_' from the setting section...
        $section = substr( $setting['section'], 14 );
        // Get the option value for displaying it in field template.
        $value = isset( $options[ $section ][ $field ] ) ? $options[ $section ][ $field ] : null;
        add_settings_field(
          $slug = $setting['section'] . '_' . $field,
          $setting['name'],
          array( $this, 'settings_fields_template' ),
          'spfs_settings_page',
          $setting['section'],
          // Send this array as argument to the settings_fields_template method.
          array(
          'section'   => $section,
          'field'     => $field,
          'slug'      => $slug,
          'value'     => $value,
          'type'      => $setting['type'],
          'require'   => isset( $setting['require'] ) ? $setting['require'] : false,
          'label_for' => $slug
          )
        );
      }
    }
  }

  /**
   * Setting's sanitization callback.
   *
   * Validate $spfs_settings array keys and values and sanitize fields values.
   * $spfs_settings is an two-dimensional array sructured this way:
   * $spfs_settings[ $section ][ $field ]
   *
   * @param  array $input SPFS admin settings.
   * @return array $output Valid/Sanitized SPFS admin settings.
   * @todo   Test it.
   */
  public function sanitization( $input ) {
    foreach ( $input as $section => $value ) {
      // Check if the '$spfs_settings[ $section ]' key exists in $this->settings_sections...
      if ( ! isset( $this->settings_sections[ $section ] ) ) {
        // If not, return without saving the settings fields values.
        return;
      }
    }
    foreach ( $input as $section => $fields ) {
      foreach ( $fields as $field => $value ) {
        // Check if the '$spfs_settings[ $section ][ $field ]' key exists in $this->settings_fields...
        if ( ! isset( $this->settings_fields[ $field ] ) ) {
          // If $field is not a known setting field slug, maybe it's because it's an additional field.
          // For reminding, additional fields can be added by $this->init_settings_fields()... For more info, refer to it.
          // So let's try to get the base slug of the field without the ID at the end...
          // Eg: 'shipper_name_1' would become 'shipper_name'.
          // On which we add '_0' to compare next to the original setting field.
          $key = substr( $field, 0, -2 ) . '_0';
          // If the field slug key doesn't exist in $this->settings_fields...
          if ( ! isset( $this->settings_fields[ $key ] ) ) {
            // Return without saving any field values.
            return;
          }
        }
        // Now that we know all '$spfs_settings' keys are valid, let's focus on settings values...
        if ( ! empty( $value ) ) {
          // Validate and sanitize the value.
          $output[ $section ][ $field ] = sanitize_text_field( stripslashes( $value ) );
        }
        else {
          // Set the value to null.
          $output[ $section ][ $field ] = null;
        }
      }
    }
    // Return the valid and sanitized settings.
    return $output;
  }

  /**
   * Plugin's settings page template.
   */
  public function settings_page_template() {
    echo '<h1>' . esc_html__( 'Service Pack for Storefront', 'service-pack-for-storefront' ) . '</h1>';
    echo '<form method="POST" action="options.php">';
    
    do_settings_sections( 'spfs_settings_page' );
    submit_button();
    settings_fields( 'spfs_settings_group' );
    
    echo '</form>';
  }

  /**
   * Plugin's settings sections template.
   *
   * @param array $args From 'add_settings_section()'.
   */
  public function settings_sections_template( $args ) { 
    // Remove section prefix 'spfs_settings_'...
    $section = substr( $args['id'], 14 );
    echo '<p>' . $this->settings_sections[ $section ]['description'] . '</p>';
  }

  /**
   * Plugin's settings fields template.
   *
   * @param array $args From 'add_settings_field()'.
   */
  public function settings_fields_template( $args ) {
    $missing_dependency = SPFS::get_instance()->is_missing_dependency( $args['require'] );
    $section            = $args['section'];
    $field              = $args['field'];
    $slug               = $args['slug'];
    $name               = 'spfs_settings[' . $section . '][' . $field . ']';
    $type               = $args['type'];
    $require            = $args['require'] ? ucfirst( $args['require'] ) : false;
    $readonly           = $missing_dependency ? 'readonly onclick="return false"' : '';
    $value              = $missing_dependency ? 0 : $args['value'];
    $description        = isset( $this->settings_fields[ $field ]['description'] ) ? $this->settings_fields[ $field ]['description'] : false;

    if ( 'checkbox' === $type ) {
      echo '<input type="hidden" name="' . $name . '" value="0">';
      echo '<input type="' . $type . '" id="' . $slug . '" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . $readonly . ' />';
      
      if ( ! empty( $description ) ) {
        echo '<label for="' . $slug . '">' . $description . $requirement = $require ? ' <strong>' . sprintf( __( 'Require %s.', 'service-pack-for-storefront' ), $require ) . '</strong>' : '' . '</label>';
      }
    }
    elseif ( 'text' === $type ) {
      $special = null;
      $style   = '';

      if ( $section === 'order_tracking' && ! empty( $value ) ) {
        $special = true;
        $style = ' style="background-color: #f2f2f2"';
      }
      echo '<input type="' . $type . '" id="' . $slug . '" name="' . $name . '" value="' . esc_attr( $value ) . '" class="regular-text"' . $style . ' />';
      
      if ( ! empty( $description ) && ! $special ) {
        echo '<p class="description">' . $description . '</p>';
      }
    }
  }
}
