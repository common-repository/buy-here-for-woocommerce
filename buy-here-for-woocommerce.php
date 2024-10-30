<?php
/*
Plugin Name: Buy Here For WooCommerce
Plugin URI: https://www.storelocatorplus.com/product/buy-here-for-woocommerce/
Description: Connect WooCommerce products to physical locations managed by Store Locator Plus.
Author: Store Locator Plus
Author URI: https://www.storelocatorplus.com
License: GPL3
Tested up to: 4.4.2
Version: 4.4

Text Domain: buy-here-for-woocommerce
Domain Path: /languages/

Copyright 2015 - 2016  Charleston Software Associates (info@storelocatorplus.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// No direct access allowed outside WordPress
//
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// Since this is an add-on for WooCommerce + Store Locator Plus
// we only want to "get started" after all plugins are loaded.
//
// This allows us to check our dependencies knowing WC and SLP
// should be loaded into the PHP stack by now.
//
// Concept Credits: @pippinsplugins, @ipstenu
// @see https://pippinsplugins.com/checking-dependent-plugin-active/
// @see https://make.wordpress.org/plugins/2015/06/05/policy-on-php-versions/
//
function BuyHereWC_loader() {

    // Requires WP Version 3.8+
    //
    global $wp_version;
    if ( version_compare( $wp_version, '3.8', '<' ) ) {
            add_action(
                    'admin_notices',
                    create_function(
                            '',
                            "echo '<div class=\"error\"><p>".
                    __( 'Buy Here for WooCommerce requires WordPress 3.8 to function properly. ' , 'buy-here-for-woocommerce' ) .
                    __( 'This plugin has been deactivated.'                                      , 'buy-here-for-woocommerce' ) .
                    __( 'Please upgrade WordPress.'                                              , 'buy-here-for-woocommerce' ) .
                "</p></div>';"
                    )
            );
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );            
        deactivate_plugins( plugin_basename( __FILE__ ) );
        return;
    }
    
    // Check Store Locator Plus is there, running, and old enough.
    // If not, tell someone.
    //
    if (
        ! class_exists( 'SLPlus' )                  ||          
        ! defined( 'SLPLUS_VERSION' )               ||
        version_compare( SLPLUS_VERSION, '4.4.17', '<' )             
    ) {
        add_action(
            'admin_notices',
            create_function(
                '',
                "echo '<div class=\"error\"><p>".
                __( 'Buy Here for WooCommerce requires Store Locator Plus version 4.4.17. ' , 'buy-here-for-woocommerce' ) .
                __( 'This plugin has been deactivated.'                                     , 'buy-here-for-woocommerce' ) .
                __( 'Please install and activate Store Locator Plus.'                       , 'buy-here-for-woocommerce' ) .
                "</p></div>';"
            )
        );
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );        
        deactivate_plugins( plugin_basename( __FILE__ ) );
        return;
    }    

    // Check WooCommerce is there, running, and old enough.
    // If not, tell someone.
    //
    if (
            ! class_exists( 'WooCommerce' )              ||             
            ! defined( 'WOOCOMMERCE_VERSION' )           ||             
            version_compare( WOOCOMMERCE_VERSION, '2.4.10', '<' ) 
    ) {
        add_action(
            'admin_notices',
            create_function(
                '',
                "echo '<div class=\"error\"><p>".
                    __( 'Buy Here for WooCommerce requires WooCommerce version 2.4.10. '    , 'buy-here-for-woocommerce' ) .
                    __( 'This plugin has been deactivated.'                                 , 'buy-here-for-woocommerce' ) .
                    __( 'Please install and activate WooCommerce.'                          , 'buy-here-for-woocommerce' ) .
                "</p></div>';"
            )
        );
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );                
        deactivate_plugins( plugin_basename( __FILE__ ) );
        return;
    }



    // Define some path constants in an attempt to bypass depth-driven confusion.
    //
    if ( ! defined( 'WCBH_DIR'      ) ) { define( 'WCBH_DIR'        , plugin_dir_path( __FILE__ )            ); }  // Fully qualified path to the directory where this loader lives.
    if ( ! defined( 'WCBH_DIR_INC'  ) ) { define( 'WCBH_DIR_INC'    , WCBH_DIR  . 'include/'                 ); }  // The include directory for this plugin.
    if ( ! defined( 'WCBH_DIR_BASE' ) ) { define( 'WCBH_DIR_BASE'   , WCBH_DIR  . 'base_class/'              ); }  // The base class directory for this plugin.
    if ( ! defined( 'WCBH_REL_DIR'  ) ) { define( 'WCBH_REL_DIR'    , plugin_basename( dirname( __FILE__ ) ) ); }  // Relative directory for this plugin in relation to wp-content/plugins
    if ( ! defined( 'WCBH_FILE'     ) ) { define( 'WCBH_FILE'       ,  __FILE__                              ); }  // FQ File name for this file.
    

    // Go forth and sprout your tentacles...
    // Get some WooCommerce goodness and sprinkle on some Store Locator Plus sauce.
    //
    require_once( 'include/class.buy_here.php' );
    WC_Buy_Here::init();
}

add_action('plugins_loaded', 'BuyHereWC_loader');
