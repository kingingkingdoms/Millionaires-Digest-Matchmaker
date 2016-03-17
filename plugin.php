<?php
/**
 * Plugin Name: WP REST API - Plugins Endpoint
 * Description: Plugin endpoints for the WP REST API
 * Author: Paul Gibbs
 * Author URI: http://byotos.com/
 * Version: 0.1.0
 * Plugin URI: https://github.com/paulgibbs/rest-api-plugins/
 * License: GPL2+
 */

/**
 * Initialise the REST API plugins controller.
 *
 * @since 0.1.0
 */
function plugins_rest_api_init() {
	if ( class_exists( 'WP_REST_Controller' ) && ! class_exists( 'WP_REST_Plugins_Controller' ) ) {
		require_once dirname( __FILE__ ) . '/lib/class-wp-rest-plugins-controller.php';
	}
}
add_action( 'rest_api_init', 'plugins_rest_api_init', 11 );
