<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Main plugin class
 * 
 * Initialize all functionalities/modules and options...
 * Include also methods used across the plugin.
 * 
 * @class    SPFS
 * @since    0.1.4
 * @package  SPFS
 * @category Core
 * @author   Opportus
 */
class SPFS {

  /**
   * @var object $instance Plugin's singleton self instance.
   */
  private static $instance;

  /**
   * @var object $settings Plugin's singleton admin settings instance.
   */
  private $settings;

  /**
   * @var object $page_content Store the current page content.
   */
  private $page_content;

  /**
   * Get SPFS instance.
   *
   * @return odject $instance Return SPFS singleton self instance.
   */
  public static function get_instance() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new SPFS();
    }
    return self::$instance;
  }

  /**
   * Initialize everything...
   */
  private function __construct() {
    $this->init_textdomain();
    $this->init_settings();
    $this->init_options();
    $this->init_modules();
    $this->init_widgets();
    $this->init_actions();
  }

  /**
   * Initialize plugin's settings.
   */
  private function init_settings() {
    include_once( SPFS_DIR . 'admin/class-spfs-settings.php' );
    $this->settings = SPFS_Settings::get_instance();
  }

  /**
   * Initialize/Update plugin's options.
   */
  private function init_options() {
    $options = get_option( 'spfs_settings' );
    
    if ( $options ) {
      foreach ( $this->settings->get_settings_fields() as $field => $setting ) {
        // Check if the current module requires Storefront or WooCommerce.
        if ( isset( $setting['require'] ) ) {
          // If the module's dependency (Storefront or WooCommerce) is not activated...
          if ( $this->is_missing_dependency( $setting['require'] ) ) {
            // Set the option 'modules_activation' to 0, so the module won't be activated by init_modules() method.
            $options['modules_activation'][ $field ] = 0;
            update_option( 'spfs_settings', $options );
          }
        }
      }
    }
    // In the case of, say, the plugin activation, the plugin's options are not set yet. So let's set them...
    elseif ( ! $options ) {
      $options = array();
      // Structure an array of 2 dimensions which will be serialized in wp_options table ("spfs_settings" row)...
      foreach ( $this->settings->get_settings_fields() as $field => $setting ) {
        // Remove section prefix "spfs_settings_"...
        $section = substr( $setting['section'], 14 );
        if ( 'modules_activation' === $section ) {
          // Check if the module requires Storefront or WooCommerce.
          if ( isset( $setting['require'] ) ) {
            // By default, deactivate modules which have missing dependencies (Storefront or WooCommerce).
            if ( $this->is_missing_dependency( $setting['require'] ) ) {
              $options[ $section ][ $field ] = 0;
            }
            else {
              $options[ $section ][ $field ] = 1;
            }
          }
          // Activate modules in any other case.
          else {
            $options[ $section ][ $field ] = 1;
          }
        }
        // Set to null other options.
        else {
          $options[ $section ][ $field ] = null;
        }
      }
      add_option( 'spfs_settings', $options );
    }
  }

  /**
   * Initialize the plugin's modules if the 'modules_activation' option value is 1.
   */
  private function init_modules() {
    $options = get_option( 'spfs_settings' );
    $modules = $options['modules_activation'];
    
    foreach ( $modules as $module => $activation ) {
      if ( $activation ) {
        include_once( SPFS_DIR . 'modules/class-spfs-' . str_replace( '_', '-', $module ) . '.php' );

        // Second parameter of ucwords() has been added in PHP version 5.5.16
        //
        // So if PHP version is equal or higher than 5.5.16...
        if ( version_compare( PHP_VERSION, '5.5.16' ) >= 0 ) {
          $class = 'SPFS_' . ucwords( $module, '_' );
        }
        // Or if it's lower...
        else {
          $class = str_replace( '_', ' ', $module );
          $class = ucwords( $class );
          $class = 'SPFS_' . str_replace( ' ', '_', $class );
        }
        new $class;
      }
    }
  }

  /**
   * Initialize plugin's widgets.
   */
  private function init_widgets() {
    add_action( 'widgets_init', array( $this, 'register_widgets' ) );
  }

  /**
   * Register plugin's widgets...
   *
   * Hooked into 'widget_init' action.
   */
  public function register_widgets() {
    include_once( SPFS_DIR . 'widgets/class-spfs-widget-facebook-page.php' );
    include_once( SPFS_DIR . 'widgets/class-spfs-widget-newsletter.php' );
    include_once( SPFS_DIR . 'widgets/class-spfs-widget-social-link.php' );
    register_widget( 'SPFS_Widget_Facebook_Page' );
    register_widget( 'SPFS_Widget_Newsletter' );
    register_widget( 'SPFS_Widget_Social_Link' );
  }

  /**
   * Initialize plugin's actions.
   */
  private function init_actions() {
    // Get the current page content.
    add_action( 'the_posts', array( $this, 'set_page_content' ) );
    add_action( 'admin_init', array( $this, 'reminder_admin_notice' ) );
  }

  /**
   * Load plugin's text domain.
   */
  private function init_textdomain() {
      load_plugin_textdomain( 'service-pack-for-storefront', false, 'service-pack-for-storefront/languages' );
  }

  //________________________________________________________
  //                                                        |
  //                                                        |
  // From here we include methods used across the plugin... |
  //                                                        |
  //________________________________________________________|
  
  /**
   * Initialize Database...
   * 
   * Hooked into 'register_activation_hook' action.
   */
  public function init_db() {
    global $wpdb;
    $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}spfs_email_list (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) NOT NULL, subscription DATETIME NOT NULL);" );
  }

  /**
   * Clean Database...
   * 
   * Hooked into 'register_deactivation_hook' action for testing purposes.
   *
   * @todo Will be hooked to 'register_uninstall_hook' action later.
   * @todo Delete 'Tracking Order' module meta from orders.
   */
  public function clean_db() {
    global $wpdb;
    // Count how many email addresses are registered on the 'spfs_email_list' table...
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}spfs_email_list" );
    // If there are no registered email addresses, drop the table, else do nothing.
    if ( count( $results ) === 0 ) {
      $wpdb->query( "DROP TABLE {$wpdb->prefix}spfs_email_list" );
    }
    delete_option( 'spfs_settings' );

    delete_transient( 'spfs_reminder_admin_notice_displayed' );
  }

  /**
   * Get the current page content.
   *
   * Apply filter to it and store it in $this->page_content.
   *
   * @param  object $posts
   * @return object $posts
   */
  public function set_page_content( $posts ) {
    apply_filters( 'spfs_page_content', $posts );
    $this->page_content = $posts;

    return $posts;
  }

  /**
   * Reminder admin notice.
   *
   * Display it once at month.
   * Hooked into 'admin_init' action.
   */
  public function reminder_admin_notice() {
    if ( ! get_transient( 'spfs_reminder_admin_notice_displayed' ) ) {
      echo '<div class="updated notice is-dismissible">';
      echo '<p>' . sprintf( esc_html__( 'Thank you for using %s, hope you enjoy it !', 'service-pack-for-storefront' ), '<strong>Service Pack for Storefront</strong>' ) . '</p>';
      echo '<p>' . sprintf( esc_html__( 'If you have a question, a suggestion, or if you need help, here is the %s.', 'service-pack-for-storefront' ), '<a href="https://wordpress.org/support/plugin/service-pack-for-storefront" target="_blank">' . esc_html__( 'support forum', 'service-pack-for-storefront' ) . '</a>' ) . '</p>';
      echo '<p>' . sprintf( esc_html__( 'To allow me to spend more time developing this plugin for you, a little %s would be great !', 'service-pack-for-storefront' ), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R8R7Y9R2C79J8" target="_blank">' . esc_html__( 'donation', 'service-pack-for-storefront' ) . '</a>'  ) . '</p>';
      echo '</div>';
      
      set_transient( 'spfs_reminder_admin_notice_displayed', 1, 30 * DAY_IN_SECONDS );
    }
  }
  
  /**
   * Get the current page content property.
   *
   * @return object SPFS::$page_content
   */
  public function get_page_content() {
    if ( isset( $this->page_content ) ) {
      return $this->page_content;
    }
  }

  /**
   * Return true if the current page content has a shortcode named $shortcode. Otherwise, return false.
   *
   * @param  string $shortcode The shortcode slug.
   * @return bool true if the current page content include a $shortcode, false otherwise.
   */
  public function page_has_shortcode( $shortcode ) {
    if ( ! shortcode_exists( $shortcode ) || empty( $this->page_content ) ) {
      return false;
    }
    foreach ( $this->page_content as $page_content ) {
      if ( has_shortcode( $page_content->post_content, $shortcode ) ) {
        return true;
      }
      else {
        return false;
      }
    }
  }

  /**
   * Test if the param $dependency is not activated...
   *
   * @param  string $dependency 'woocommerce' or 'storefront'
   * @return bool true if $dependency is not activated, false otherwise.
   */
  public function is_missing_dependency( $dependency ) {
    if ( ! $dependency ) {
      return false;
    }
    elseif ( 'storefront' === $dependency ) {
      if ( 'storefront' === strtolower( wp_get_theme()->template ) ) {
        return false;
      }
      else return true;
    }
    elseif ( 'woocommerce' === $dependency ) {
      if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        return false;
      }
      else return true;
    }
  }
}
