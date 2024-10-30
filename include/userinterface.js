/**
 * @package StoreLocatorPlus\BuyHere
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2016 Charleston Software Associates, LLC
 */

/* global slp_Filter */

// Experience Namespace
//
var BUYHERE = BUYHERE || {};

/**
 * Location List management
 */
BUYHERE.location_list = {

    /**
     * Create formatted product output for hte [slp_location ...] shortcodes in results layout.
     * 
     * @param {type} location_data
     * @returns {undefined}
     */
    create_product_output: function( location_data ) {
        var separator = ' , ';  // TODO: make this an admin interface setting
        location_count = location_data.length;
        for (var location_num = 0 ; location_num < location_count ; ++location_num ) {
            if ( location_data[ location_num ].products ) {
                location_data[ location_num ].products.list = '';
                var inline_separator = '';
                for ( var id in location_data[ location_num ].products ) {
                    if ( id && ( typeof location_data[ location_num ].products[ id ].name !== 'undefined' ) ) {
                        if ( location_data[ location_num ].products.list !== '' ) {
                            inline_separator = separator;
                        }
                        location_data[ location_num ].products.list +=  inline_separator + location_data[ location_num ].products[ id ].name;
                    }
                }
            }
        }
    },
    
    /**
     * Subscribe to the slp.js location string related filters.
     *
     * @see https://api.jquery.com/jQuery.Callbacks/
     */
    setup_subscriptions: function () {
        slp_Filter('locations_found').subscribe( this.create_product_output );        
    }    
};

// Document Ready
//
jQuery( document ).ready(
    function () {
        BUYHERE.location_list.setup_subscriptions();
    }
);
