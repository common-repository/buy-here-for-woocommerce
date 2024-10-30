<?php
if (! class_exists('WCBH_UI')) {
    require_once( SLPLUS_PLUGINDIR . 'include/base_class.userinterface.php');

    /**
     * Class WCBH_UI
     *
     * @package WCBH\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2016 Charleston Software Associates, LLC
     *
     * Text Domain: buy-here-for-woocommerce
     *
     * @property        WC_Buy_Here     $addon
     */
    class WCBH_UI extends SLP_BaseClass_UI {
        public  $addon;

        /**
         * Add UI-centric hooks and filters.
         */
        public function add_hooks_and_filters() {
            parent::add_hooks_and_filters();
            add_filter( 'slp_javascript_results_string' , array( $this , 'add_product_list_to_results' ) , 95 );    // Experience = 90 (for now) and loads from options init, overwriting all.
        }

        /**
         * Put the product list into the results layout if it is not already in there.
         * 
         * @param   string $layout      The current results layout.
         * @return  string              The modified results layout.
         */
        public function add_product_list_to_results( $layout ) {            
            if ( $this->layout_has_product_list( $layout ) ) { return $layout; }    // no modification required            
            return $this->place_products_at_tertiatry_bottom( $layout );
        }

        /**
         * Return true if [slp_location products.list] , or a valid variant, is already in the layout.
         * 
         * @param type $layout
         * @return boolean
         */
        private function layout_has_product_list( $layout ) {
            $tag = 'slp_location';
            preg_match_all( '/' . get_shortcode_regex( array( $tag ) ) . '/' , $layout , $matches, PREG_SET_ORDER );
            if ( empty( $matches) ) { return false; }
            foreach ( $matches as $shortcode ) {
                if ( $tag === strtolower( $shortcode[2] ) ) { 
                    foreach ( $shortcode as $attribute ) {
                        if ( 'products.list' === strtolower( $attribute)  ) { return true; }
                    }
                }
            }
            return false;
        }

        /**
         * Insert [slp_location products.list] at [slp_addon section=tertiary position=last] in the results layout.
         * 
         * @param   string $layout      The current results layout.
         * @return  string              The modified results layout.
         */
        private function place_products_at_tertiatry_bottom( $layout ) {
            add_shortcode( 'slp_addon' , array( $this , 'slp_addon_shortcode_for_product_list' ) );
            $new_layout = do_shortcode( $layout );
            remove_shortcode( 'slp_addon' );
            return $new_layout;
        }

        /**
         * Rebuild the slp_addon shortcode.
         * 
         * @param   array $atts     key = the attribute slug, value = the attribute value
         * @return  string          a re-constitued shortcode for slp_addon.
         */
        private function rebuild_slp_addon_shortcode( $atts ) {            
            $att_string = '';
            foreach ( $atts as $key=>$value ) {
                $att_string .= " {$key}={$value} ";
            }
            return "[slp_addon {$att_string}]";
        }

        /**
         * Use the WP shortcode processor to replace [slp_addon section=tertiary postion=last] with the [slp_location products.list] for the results layout.
         * @param array $atts
         */
        function slp_addon_shortcode_for_product_list( $atts ) {            
            if ( ! isset( $atts['section'] )     ) { return $this->rebuild_slp_addon_shortcode( $atts ); }
            if ( $atts['section'] !== 'tertiary' ) { return $this->rebuild_slp_addon_shortcode( $atts ); }

            if ( ! isset( $atts['position'] )    ) { return $this->rebuild_slp_addon_shortcode( $atts ); }
            if ( $atts['position'] !== 'last'    ) { return $this->rebuild_slp_addon_shortcode( $atts ); }

            return '[slp_location products.list][slp_addon section=tertiary position=last]';
        }
    }
}