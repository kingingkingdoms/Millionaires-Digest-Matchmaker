<?php
defined( 'ABSPATH' ) || exit;

/**
 * Plugin endpoints.
 *
 * @since 0.1.0
 */
class WP_REST_Plugins_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'plugins';

		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}

	/**
	 * Register the plugin routes.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[-_%\.\w]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			/*array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),*/
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get the plugin schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'plugin',
			'type'       => 'object',


			/*
			 * Base properties for every plugin.
			 */

			'properties' => array(
				'id' => array(
					'description' => __( 'A unique alphanumeric identifier for the object.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),

				'name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the object.' ),
					'readonly'    => true,
					'type'        => 'string',
				),

				'description' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The description of the object.' ),
					'readonly'    => true,
					'type'        => 'string',
				),

				'version' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The version of the object (x.y.z).' ),
					'readonly'    => true,
					'type'        => 'string',
				),

				'link' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'URL to the website of the object.' ),
					'format'      => 'uri',
					'readonly'    => true,
					'type'        => 'string',
				),

				'author' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the author(s) of the object.' ),
					'readonly'    => true,
					'type'        => 'string',
				),

				'author-url' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'URL to the author(s) of the object.' ),
					'format'      => 'uri',
					'readonly'    => true,
					'type'        => 'string',
				),

				'textdomain' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the gettext text domain for the object, used for translations.' ),
					'readonly'    => true,
					'type'        => 'string',
				),

				'textdomain-path' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The relative path to the location containing the gettext translation files for the object.' ),
					'readonly'    => true,
					'type'        => 'string',
				),

				'network-only' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Whether the object can only be activated on a per-network basis.' ),
					'readonly'    => true,
					'type'        => 'boolean',
				),

				'status' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A named status for the object.' ),
					'enum'        => array( 'dropin', 'inactive', 'mustuse', 'network', 'site', ),
					'readonly'    => true,
					'type'        => 'string',
				),
			)
		);

		return $schema;
	}


	/**
	 * Get the query params for collections of plugins.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

	/**
	 * Retrieve plugins.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Reques List of plugin object data.
	 */
	public function get_items( $request ) {
		$all_plugins = array(
			'all'     => apply_filters( 'all_plugins', get_plugins() ),
			'dropin'  => get_dropins(),
			'mustuse' => get_mu_plugins(),
		);

		$plugins = array();

		foreach ( $all_plugins as $type => $_plugins ) {
			foreach ( $_plugins as $id => $plugin ) {
				$plugin['id'] = $this->get_plugin_id( $id );

				if ( $type === 'dropin' || $type === 'mustuse' ) {
					$plugin['status'] = $type;
				}

				$data = $this->prepare_item_for_response( $plugin, $request );
				$plugins[] = $this->prepare_response_for_collection( $data );
			}
		}

		return rest_ensure_response( $plugins );
	}

	/**
	 * Retrieve custom field object.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request|WP_Error Plugin object data on success, WP_Error otherwise.
	 */
	public function get_item( $request ) {
		$all_plugins = array(
			'all'     => apply_filters( 'all_plugins', get_plugins() ),
			'dropin'  => get_dropins(),
			'mustuse' => get_mu_plugins(),
		);

		$requested_plugin = array();

		foreach ( $all_plugins as $type => $_plugins ) {
			foreach ( $_plugins as $id => $plugin ) {

				// Check if requested ID matches a plugin.
				if ( $request['id'] !== $this->get_plugin_id( $id ) ) {
					continue;
				}

				// Build response data.
				$plugin['id'] = $this->get_plugin_id( $id );

				if ( $type === 'dropin' || $type === 'mustuse' ) {
					$plugin['status'] = $type;
				}

				$requested_plugin = $this->prepare_item_for_response( $plugin, $request );
				break;
			}
		}

		if ( ! $requested_plugin ) {
			return new WP_Error( 'rest_plugin_invalid_id', __( 'Invalid plugin ID.' ), array( 'status' => 404 ) );
		}

		return $requested_plugin;
	}

	/**
	 * Prepares plugin data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $plugin Plugin data.
	 * @param WP_REST_Request $request
	 * @param boolean $is_raw Is the value field still serialized? (False indicates the value has been unserialized)
	 * @return WP_REST_Response|WP_Error Plugin object data on success, WP_Error otherwise
	 */
	public function prepare_item_for_response( $plugin, $request, $is_raw = false ) {
		$data = array(
			'author'          => wp_strip_all_tags( $plugin['Author'] ),
			'author-url'      => wp_strip_all_tags( $plugin['AuthorURI'] ),
			'description'     => wp_strip_all_tags( $plugin['Description'] ),
			'id'              => wp_strip_all_tags( $plugin['id'] ),
			'name'            => wp_strip_all_tags( $plugin['Name'] ),
			'link'            => wp_strip_all_tags( $plugin['PluginURI'] ),
			'network-only'    => (bool) $plugin['Network'],
			'textdomain'      => wp_strip_all_tags( $plugin['TextDomain'] ),
			'textdomain-path' => wp_strip_all_tags( $plugin['DomainPath'] ),
			'version'         => wp_strip_all_tags( $plugin['Version'] ),
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $plugin ) );

		/**
		 * Filter a plugin value returned from the API.
		 *
		 * Allows modification of the plugin value right before it is returned.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request  Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_plugin_value', $response, $request );
	}

	/**
	 * Check if a given request has access to get information about a specific plugin.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to plugin information.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		if ( is_multisite() && ! current_user_can( 'manage_network_plugins' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 0.1.0
	 *
	 * @param array $plugin Plugin data.
	 * @return array Links for the given plugin.
	 */
	protected function prepare_links( $post ) {
		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self' => array(
				'href'   => rest_url( $base . wp_strip_all_tags( $post['id'] ) ),
			),
			'collection' => array(
				'href'   => rest_url( $base ),
			)
		);

		return $links;
	}

	/**
	 * Convert the supplied plugin path/file name into an ID.
	 *
	 * @since 0.1.0
	 *
	 * @param string $plugin
	 * @return string
	 */
	protected function get_plugin_id( $plugin ) {
		return urlencode( str_replace( '.php', '', $plugin ) );
	}
}
