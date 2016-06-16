<?php
/**
 * REST API Webhooks controller
 *
 * Handles requests to the /webhooks/<webhook_id>/deliveries endpoint.
 *
 * @author   WooThemes
 * @category API
 * @package  WooCommerce/API
 * @since    2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Webhook Deliveries controller class.
 *
 * @package WooCommerce/API
 * @extends WC_REST_Controller
 */
class WC_REST_Webhook_Deliveries_Controller extends WC_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'webhooks/(?P<webhook_id>[\d]+)/deliveries';

	/**
	 * Register the routes for webhook deliveries.
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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Check whether a given request has permission to read webhook deliveries.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_post_permissions( 'shop_webhook', 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to read a webhook develivery.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		$post = get_post( (int) $request['webhook_id'] );

		if ( $post && ! wc_rest_check_post_permissions( 'shop_webhook', 'read', $post->ID ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get all webhook deliveries.
	 *
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function get_items( $request ) {
		$webhook = new WC_Webhook( (int) $request['webhook_id'] );

		if ( empty( $webhook->post_data->post_type ) || 'shop_webhook' !== $webhook->post_data->post_type ) {
			return new WP_Error( 'woocommerce_rest_webhook_invalid_id', __( 'Invalid webhook id.', 'woocommerce' ), array( 'status' => 404 ) );
		}

		$logs = $webhook->get_delivery_logs();

		$data = array();
		foreach ( $logs as $log ) {
			$delivery = $this->prepare_item_for_response( (object) $log, $request );
			$delivery = $this->prepare_response_for_collection( $delivery );
			$data[]   = $delivery;
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get a single webhook delivery.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$id      = (int) $request['id'];
		$webhook = new WC_Webhook( (int) $request['webhook_id'] );

		if ( empty( $webhook->post_data->post_type ) || 'shop_webhook' !== $webhook->post_data->post_type ) {
			return new WP_Error( 'woocommerce_rest_webhook_invalid_id', __( 'Invalid webhook id.', 'woocommerce' ), array( 'status' => 404 ) );
		}

		$log = $webhook->get_delivery_log( $id );

		if ( empty( $id ) || empty( $log ) ) {
			return new WP_Error( 'woocommerce_rest_invalid_id', __( 'Invalid resource id.', 'woocommerce' ), array( 'status' => 404 ) );
		}

		$delivery = $this->prepare_item_for_response( (object) $log, $request );
		$response = rest_ensure_response( $delivery );

		return $response;
	}

	/**
	 * Prepare a single webhook delivery output for response.
	 *
	 * @param stdClass $log Delivery log object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $log, $request ) {
		$data = (array) $log;

		// Add timestamp.
		$data['date_created'] = wc_rest_prepare_date_response( $log->comment->comment_date_gmt );

		// Remove comment object.
		unset( $data['comment'] );

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $log ) );

		/**
		 * Filter webhook delivery object returned from the REST API.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param stdClass         $log      Delivery log object used to create response.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'woocommerce_rest_prepare_webhook_delivery', $response, $log, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param stdClass $log Delivery log object.
	 * @return array Links for the given webhook delivery.
	 */
	protected function prepare_links( $log ) {
		$webhook_id = (int) $log->request_headers['X-WC-Webhook-ID'];
		$base       = str_replace( '(?P<webhook_id>[\d]+)', $webhook_id, $this->rest_base );
		$links      = array(
			'self' => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $base, $log->id ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $base ) ),
			),
			'up' => array(
				'href' => rest_url( sprintf( '/%s/webhooks/%d', $this->namespace, $webhook_id ) ),
			),
		);

		return $links;
	}

	/**
	 * Get the Webhook's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'webhook_delivery',
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'duration' => array(
					'description' => __( 'The delivery duration, in seconds.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'summary' => array(
					'description' => __( 'A friendly summary of the response including the HTTP response code, message, and body.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'request_url' => array(
					'description' => __( 'The URL where the webhook was delivered.', 'woocommerce' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'request_headers' => array(
					'description' => __( 'The URL where the webhook was delivered.', 'woocommerce' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'request_headers' => array(
					'description' => __( 'Request headers.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'request_body' => array(
					'description' => __( 'Request body.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'response_code' => array(
					'description' => __( 'The HTTP response code from the receiving server.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'response_message' => array(
					'description' => __( 'The HTTP response message from the receiving server.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'response_headers' => array(
					'description' => __( 'Array of the response headers from the receiving server.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'response_body' => array(
					'description' => __( 'The response body from the receiving server.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created' => array(
					'description' => __( "The date the webhook delivery was logged, in the site's timezone.", 'woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}
}
