<?php

/**
 * Plugin Name: Service Pack for Storefront
 * Plugin URI: https://github.com/opportus/service-pack-for-storefront
 * Description: Adds modulable basic functionalities to your WooCommerce/Storefront site.
 * Version: 0.1.6
 * Author: opportus
 * Requires at least: 4.4
 * Tested up to 4.5.3
 * Text Domain: service-pack-for-storefront
 *
 * @category Core
 * @author   Opportus
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accesed directly.
}

/**
 * Define the plugin's constants.
 */
define( 'SPFS_VERSION', '0.1.6' );
define( 'SPFS_DIR', plugin_dir_path( __FILE__ ) . '/' );
define( 'SPFS_URL', plugins_url( '/', __FILE__ ) );

// Include the main plugin class.
require_once( SPFS_DIR . 'class-spfs.php' );

// Get the singleton instance of the main plugin class.
$SPFS = SPFS::get_instance();

/**
 * Activation/Deactivation actions.
 *
 * @see  SPFS::init_db()
 * @see  SPFS::clean_db()
 * @todo Use 'register_uninstall_hook' instead of 'register_deactivation_hook' later.
 */
register_activation_hook( __FILE__, array( $SPFS, 'init_db' ) );
register_deactivation_hook( __FILE__, array( $SPFS, 'clean_db' ) );
