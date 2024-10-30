<?php

if ( ! class_exists('WCBH_Admin') ) {
    require_once( SLPLUS_PLUGINDIR . 'include/base_class.admin.php');

    /**
     * Class WCBH_Admin
     *
     * @package WCBH\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2016 Charleston Software Associates, LLC
     *
     * Text Domain: buy-here-for-woocommerce
     *
     * @property        WC_Buy_Here     $addon
     * @property-read   array           $current_product_locations      Current list of product locations in a named array.
     *
     */
    class WCBH_Admin extends SLP_BaseClass_Admin {
        public  $addon;        
        private $current_product_locations;

        /**
         * Our admin_init hooks and filters.
         */
        public function add_hooks_and_filters() {
            if ( ! $this->being_deactivated() ) {
                parent::add_hooks_and_filters();
                add_action( 'current_screen' , array( $this , 'add_screen_hooks_and_filters' ) );            
            }
        }
        
        /**
         * Set our hooks and filter that like to have a screen ID available.
         */
        public function add_screen_hooks_and_filters() {
            $this->set_slp_hooks();
            $this->set_woocommerce_hooks();            
        }

        /**
         * Setup our admin hooks for SLP.
         */
        private function set_slp_hooks() {
            add_filter( 'wpcsl_admin_slugs' , array( $this, 'set_product_add_edit_as_slp_admin_page' ) );                        
        }
        
        /**
         * Set up our admin hooks for Woocommerce.
         */
        private function set_woocommerce_hooks() {
            global $pagenow;
            if ( $this->slplus->AdminUI->is_our_admin_page( $pagenow ) ) {
                add_filter( 'woocommerce_product_data_tabs'   , array( $this , 'add_wc_locations_tab'    )          );    // Add the subpanel tab UI
                add_action( 'woocommerce_product_data_panels' , array( $this , 'render_wc_locations_div' )          );    // Render the locations tab content
                add_action( 'woocommerce_process_product_meta', array( $this , 'save_product_locations'  ) , 15 , 2 );    // Save the location metadata
            }
        }

        /**
         * Add a new tab to the WooCommerce products subtabs.
         *
         * @param array $tabs
         *
         * @return mixed
         */
         function add_wc_locations_tab( $tabs ) {
            $tabs['slp_locations'] = array(
                'label' 	=> __( 'Locations', 'buy-here-for-woocommerce' ),
                'target' 	=> 'slp_locations',
                'class'     => array()

            );

            return $tabs;
        }

        /**
         * Render the locations subpanel for a WooCommerce product.
         */
        function render_wc_locations_div() {            
            $input_placeholder = __( 'Search for a location by name or zip code.', 'buy-here-for-woocommerce' );            
            ?>
                <div id="slp_locations" class="panel woocommerce_options_panel">
                    <div class="options_group">
                        <p class="form-field">                            
                            <label for="location_ids"><?php _e( 'Locations', 'buy-here-for-woocommerce' ); ?></label>
                            <input type="hidden" class="wc-product-search" style="width: 50%;" id="location_ids" name="location_ids" 
                                   data-placeholder="<?=$input_placeholder?>" 
                                   data-action="woocommerce_json_search_locations"
                                   data-multiple="true"
                                   data-exclude="<?php global $post; echo intval( $post->ID ); ?>"
                                   data-selected="<?php echo $this->get_current_product_locations( 'ids'  ); ?>"
                                   value="<?php echo $this->get_current_product_locations( 'keys' ); ?>"
                            />
                            <?php echo wc_help_tip( __( 'Choose the Store Locator Plus locations that you wish to have linked to this product.', 'buy-here-for-woocommerce' ) ); ?>
                        </p>
                    </div>
                </div>
            <?php                        
            return;
        }

        /**
         * Fetch the current location data.
         *
         * @param string    $type   'ids' || 'keys'
         *
         * @return string
         */
        function get_current_product_locations( $type ) {

            /**
             * @var WP_Post $post
             */
            global $post;


            // If the we have not fetched the locations for this post, get them now.
            //
            if ( ! isset( $this->current_product_locations[ $post->ID ] ) ) {
                $location_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_location_ids', true ) ) );
                $json_ids     = array();

                foreach ( $location_ids as $location_id ) {
                    $location = $this->slplus->currentLocation->get_location( $location_id );
                    if ( is_object( $location ) ) {
                        $json_ids[ $location_id ] = wp_kses_post( html_entity_decode( $location->get_formatted_name(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
                    }
                }

                $this->current_product_locations[ $post->ID ] = $json_ids;
            }

            if ( $type === 'ids' ) {
                return esc_attr( json_encode(  $this->current_product_locations[ $post->ID ] ) );
            } else {
                return implode( ',', array_keys( $this->current_product_locations[ $post->ID ] ) );
            }
        }
        
        /**
         * Save the metabox location data.
         * 
         * @param type $post_id
         * @param type $post
         */
        public function save_product_locations( $post_id, $post) {
            $locations  = isset( $_POST['location_ids'] ) ? array_filter( array_map( 'intval', explode( ',', $_POST['location_ids'] ) ) ) : array();
            update_post_meta( $post_id, '_location_ids', $locations );
        }
        
        /**
         * Add our admin pages to the valid admin page slugs.
         *
         * @param string[] $slugs admin page slugs
         * @return string[] modified list of admin page slugs
         */
        public function set_product_add_edit_as_slp_admin_page( $slugs ) {
            if ( ! $this->is_product_page() ) { return $slugs; }
            return array_merge( $slugs, array( 'post.php', 'post-new.php' ) );
        }        
        
        /**
         * Are we on a product add/edit page for WooCommerce
         */
        private function is_product_page() {
		$current_screen = get_current_screen();
                return ($current_screen->post_type === 'product');
        }
    }

}
