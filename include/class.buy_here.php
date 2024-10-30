<?php

if ( ! class_exists('WC_Buy_Here') ) {

    require_once( SLPLUS_PLUGINDIR . 'include/base_class.addon.php');

    /**
     * Class WC_Buy_Here
     *
     * @package WCBH
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2015 - 2016 Charleston Software Associates, LLC
     *
     * @property        WCBH_General_Handler    $general_handler    The "hook up" for WCBH for any WP mode.
     * @property        WCBH_Admin_Handler      $admin_handler      The "hook up" for WP in admin mode.
     * @property        WCBH_Ajax_Handler       $ajax_handler       The "hook up" for WP in AJAX mode.
     * @property        WC_Buy_Here             $instance           It's one of us.
     */
    class WC_Buy_Here extends SLP_BaseClass_Addon {

        /**
         * Initialize a singleton of this object.
         *
         * @return WC_Buy_Here
         */
        public static function init() {
            static  $instance = false;
            if ( ! $instance ) {
                load_plugin_textdomain('buy-here-for-woocommerce', false, WCBH_REL_DIR . '/languages/');
                $instance = new WC_Buy_Here(
                        array(
                            'version'           => '4.4',
                            'min_slp_version'   => '4.4.17',

                            'name'              => __( 'Buy Here for WooCommerce' , 'buy-here-for-woocommerce' ),
                            'option_name'       => 'buy-here-wc'                     ,
                            'file'              => WCBH_FILE                         ,

                            'admin_class_name'          => 'WCBH_Admin',
                            'ajax_class_name'           => 'WCBH_Ajax',
                            'userinterface_class_name'  => 'WCBH_UI',                            
                        )                        
                    );
            }
            return $instance;
        }

    }

}