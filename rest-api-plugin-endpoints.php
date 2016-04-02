<?php
/**
 * Plugin Name: WP REST API - Plugin Endpoints
 * Description: Plugin endpoints for WordPress' REST API.
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

		$controller = new WP_REST_Plugins_Controller();
		$controller->register_routes();
	}
}
add_action( 'rest_api_init', 'plugins_rest_api_init', 11 );
