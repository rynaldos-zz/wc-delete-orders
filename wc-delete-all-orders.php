<?php

/*
 Plugin Name: WC Delete orders
 Plugin URI: https://profiles.wordpress.org/rynald0s
 Description: This plugin lets you nuke all orders — please use with caution! 
 Author: Rynaldo Stoltz
 Author URI: https://github.com/rynaldos
 Version: 1.0
 License: GPLv3 or later License
 License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

 if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

if (!class_exists('WooCommerce_Delete_all_orders')) {
  class WooCommerce_Delete_all_orders {
    public static $instance;

    public static function init() {
      if ( is_null( self::$instance ) ) {
        self::$instance = new WooCommerce_Delete_all_orders();
      }
      return self::$instance;
    }

    private function __construct() {
      add_filter( 'admin_init', array( $this, 'handle_woocommerce_tool' ) );
      add_filter( 'woocommerce_debug_tools', array( $this, 'add_woocommerce_tool' ) );
    }

    /**
     * Runs an SQL query in the database, which deletes all orders
     */

    public function wc_delete_orders() {
      global $wpdb;
      $ran = true;
      $sql = "DELETE FROM wp_woocommerce_order_itemmeta";
      $sql = "DELETE FROM wp_woocommerce_order_items";
      $sql = "DELETE FROM wp_posts WHERE post_type = 'shop_order'";
      $rows = $wpdb->query( $sql );

      if( false !== $rows ) {
        $this->deleted = $rows;
        //add_action( 'admin_notices', array( $this, 'admin_notice_success' ) );
      }
    }

    /**
     * Adds a tool to the WooCommerce tools
     */

    public function add_woocommerce_tool( $tools ) {
      $tools['wc_delete_orders'] = array(
        'name'    => __( 'Delete all orders', 'woocommerce' ),
        'button'  => __( 'Delete all orders', 'woocommerce' ),
        'desc'    => __( 'This option will delete all your orders — please use with caution!', 'woocommerce' ),
        'callback' => array( $this, 'debug_notice_success' ),
      );
      return $tools;
    }

    /**
     * Runs the tool
     *
     * The tool button, when clicked, will send a GET request to the tab page
     * along with &action=wc_delete_orders
     */

    public function handle_woocommerce_tool() {
      if( empty( $_REQUEST['page'] ) || empty( $_REQUEST['tab'] ) ) {
          return;
      }
      
      // check that we are on woocommerce system status admin page
      if( 'wc-status' != $_REQUEST['page'] ) {
        return;
      }

      // check that we are on the tools tab
      if( 'tools' != $_REQUEST['tab'] ) {
        return;
      }

      // check permissions
      if( ! is_user_logged_in() || ! current_user_can('manage_woocommerce') ) {
        return;
      }

      if ( ! empty( $_GET['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'debug_action' ) ) {
        if( $_GET['action'] === 'wc_delete_orders' ) {
          $this->wc_delete_orders();
        }
      }
    }

    /**
     * Admin notification after running the tool
     */

    public function debug_notice_success( ) {
      $deleted = $this->deleted;
    ?>
<div class="notice notice-success is-dismissible">
  <p><?php echo wp_sprintf( __('%d orders were deleted.', 'woocommerce'), $deleted ); ?></p>
</div>
    <?php
    }
  }
}

// init plugin
$woocommerce_delete_orders = WooCommerce_Delete_all_orders::init();

