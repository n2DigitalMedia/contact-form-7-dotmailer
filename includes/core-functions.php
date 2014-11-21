<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Dotmailer Contact Form 7 Plugin Core Functions
 *
 * Functions for determining the current query/page.
 *
 * @category 	Core
 * @package 	DMCF7PLUGIN/Functions
 * @version     
 * @author 		n2 Digital Media
 */

/**
 * Get Plugin Options/Settings. Adding defaults if not set.
 *
 * @return array
 */

function n2dmcf7_get_options() {

	$defaults = array();

	return $dm_settings = wp_parse_args(get_option('n2dmcf7_dotmailer_settings'), $defaults);
}

if(!function_exists('_log')){
  	function _log( $message ) {
    	if( WP_DEBUG === true ){
      		if( is_array( $message ) || is_object( $message ) ){
        		error_log( print_r( $message, true ) );
      		} else {
        		error_log( $message );
      		}
    	}
  	}
}


?>