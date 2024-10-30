<?php

if ( ! class_exists('WCBH_Ajax') ) {
    require_once( SLPLUS_PLUGINDIR . 'include/base_class.admin.php');

    /**
     * Class WCBH_Ajax
     *
     * @package WCBH\Ajax
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2016 Charleston Software Associates, LLC
     *
     * Text Domain: buy-here-for-woocommerce
     * 
     * @property        SLPlus          $slplus
     * @property        WC_Buy_Here     $addon
     *
     */
    class WCBH_Ajax extends SLP_BaseClass_AJAX {
        public $addon;

        /**
         * Things we do to latch onto an AJAX processing environment.
         *
         * Add WordPress and SLP hooks and filters only if in AJAX mode.
         *
         * WP syntax reminder: add_filter( <filter_name> , <function> , <priority> , # of params )
         *
         * Remember: <function> can be a simple function name as a string
         *  - or - array( <object> , 'method_name_as_string' ) for a class method
         * In either case the <function> or <class method> needs to be declared public.
         *
         * @link http://codex.wordpress.org/Function_Reference/add_filter
         *
         */
	public function do_ajax_startup() {
            $this->valid_actions[] = 'woocommerce_json_search_locations';
            if ( ! $this->is_valid_ajax_action() ) { return; }
            $this->add_action_handlers();            
	}

        /**
         * Add Action Handlers
         */
        private function add_action_handlers() {
            add_action( 'wp_ajax_woocommerce_json_search_locations' , array( $this , 'json_search_locations'        ) );
            add_filter( 'slp_results_marker_data'                   , array( $this , 'add_products_to_marker_data'  ) );
        }
        
        /**
         * Add products to the marker data.
         * 
         * @param   array       $marker     The map marker data.
         * 
         * @return  array                   The modified map marker.
         */
        public function add_products_to_marker_data( $marker ) {
            // See if any products are linked to this location.
            $location_products = $this->get_products_for_location( );
            
            if ( ! empty( $location_products ) ) {
                $marker['products'] = $location_products;
            }
            
            return $marker;
        }
        
        /**
         * Get the list of products associated with this location.
         * 
         * @return  array
         */
        private function get_products_for_location( ) {
            $args = array(
                'post_type'     => array( 'product', 'product_variation' ),
                'posts_per_page'=> -1,
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'orderby'       => 'parent title',
                'meta_query'    => array(
                    array(
                        'key'       => '_location_ids' ,
                        'value'     => ';i:'.$this->slplus->currentLocation->id.';',  // serialized JSON int of location ID
                        'compare'   => 'LIKE'
                    )
                )
            );
            $posts = get_posts( $args );
            $found_products = array();

            if ( ! empty( $posts ) ) {
                foreach ( $posts as $post ) {
                    $product = wc_get_product( $post->ID );

                    if ( ! current_user_can( 'read_product', $post->ID ) ) {
                        continue;
                    }

                    $found_products[ $post->ID ] = array(
                        'name'      => $product->get_formatted_name() ,
                        'buy_link'  => sprintf( '<a href="%s">%s</a>',$product->add_to_cart_url(),$product->add_to_cart_text()),
                        'price'     => $product->get_price(),
                        'sku'       => $product->get_sku()
                    );
                }
            }            

            return $found_products;
        }
        
        /**
         * Search for Store Locator Plus locations and echo JSON.
         */
        public function json_search_locations() {
            $term = (string) wc_clean( stripslashes( $_GET['term'] ) );            
            if ( empty( $term ) ) { die(); }
            
            // Build query to find matching locations
            //
            global $wpdb;
            $found_locations = array();
            $like_term = '%' . $wpdb->esc_like( $_GET[ 'term' ] ) . '%' ;
            add_filter( 'slp_extend_get_SQL' , array( $this , 'select_where_location_has_name_or_zip' ) );            
            $offset = 0;
            $data = $this->slplus->database->get_Record(array('selectall','where_has_name_or_zip') , array( $like_term , $like_term ), $offset++ );
            while ( ! empty( $data ) ) {
                $found_locations[ $data['sl_id'] ] = $data['sl_store'];
                $data = $this->slplus->database->get_Record(array('selectall','where_has_name_or_zip') , array( $like_term, $like_term ) , $offset++ );                
            }
                
            /**
             * FILTER: woocommerce_json_search_locations_found on the AJAX location search
             * 
             * @params  array   $found_locations    key = location id, value = location name
             * @returns array                       A modified find locations array.
             */
            $found_locations = apply_filters( 'woocommerce_json_search_locations_found', $found_locations );            
            wp_send_json( $found_locations );
        }
        
        /**
         * Only select locations with option values set.
         *
         * @param $command
         * @return string
         */
        public function select_where_location_has_name_or_zip( $command ) {
            if ( $command !== 'where_has_name_or_zip' ) { return $command; }
            return $this->slplus->database->add_where_clause(" ( sl_store LIKE %s ) OR ( sl_zip LIKE %s ) ");
        }        
    }
}
