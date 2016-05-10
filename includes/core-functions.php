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

?>