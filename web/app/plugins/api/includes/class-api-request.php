<?php

/**
 * API Class for all requests.
 */
class LM_API_Request {

	public $api_ver;
	public $controller;
	public $action;
	public $resolved_action;
	public $request_method;
	public $body;

	// Allowed HTTP methods.
	protected $_allowed_request_types = [ 'GET', 'POST', 'DELETE', 'PUT' ];

	// For now we must use hardcoded api_key.
	// Defied in configs or main api.php file.
	protected $_api_key = LM_API_KEY;

	/**
	 * Class constructor.
	 * @param array $request
	 * @return object
	 */
	public function __construct( $request ) {
		// After validation we can setup class object
		$this->setup_object( $request );
	}

	/**
	 * Validate request.
	 * @return object|array WP_Error if validation failed or array if all ok.
	 */
	public function validate() {
		// Simple authorization via api_key
		if ( ! $this->check_authorization() ) {
			return new WP_Error( '401', 'Unauthorized.' );
		}

		// If incorrect request_type?
		if ( ! $this->check_request_method() ) {
			return new WP_Error( '405', 'Check request\'s method.' );
		}

		// If incorrect API version?
		if ( ! $this->check_version() ) {
			return new WP_Error( '400', 'Check API version.' );
		}

		// If incorrect controller?
		if ( ! $this->check_controller() ) {
			return new WP_Error( '400', 'Check request\'s controller.' );
		}

		// If incorrect action?
		if ( ! $this->check_action() ) {
			return new WP_Error( '400', 'Check request\'s action.' );
		}

		// Load validator based on controller.
		$this->load_validator();

		// Errors not found.
		return true;
	}

	/**
	 * Very simple check authorization via API_KEY from request.
	 * @return bool
	 */
	private function check_authorization() {
		return ( ! empty( $_REQUEST['api_key'] ) && $_REQUEST['api_key'] == $this->_api_key );
	}


	/**
	 * Check API version.
	 * @return bool
	 */
	private function check_version() {
		return (
			! empty( $this->api_ver ) &&
			LM_API_Helper::get_api_version( $this->api_ver ) == LM_API_Helper::get_api_version( LM_API_VERSION )
		);
	}

	/**
	 * Check if controller isset and really exist.
	 * @return bool
	 */
	private function check_controller() {
		return ( ! empty( $this->controller ) && LM_API_Helper::controller_exist( $this->controller, $this->api_ver ) );
	}

	/**
	 * Check controller's action exist and it's public.
	 * @return bool
	 */
	private function check_action() {
		if ( empty( $this->resolved_action ) ) {
			return false;
		}

		// Load controller's file.
		LM_API_Helper::load_controller( $this->controller, $this->api_ver );

		// Check is method exist.
		if ( ! method_exists( ucfirst( $this->controller ), $this->resolved_action ) ) {
			return false;
		}

		// Check is method is public.
		if ( ! is_callable( [ ucfirst( $this->controller ), $this->resolved_action ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check request_method.
	 * @return bool
	 */
	private function check_request_method() {
		return (
			! empty(  $_SERVER['REQUEST_METHOD'] ) &&
			in_array( $_SERVER['REQUEST_METHOD'], $this->_allowed_request_types )
		);
	}

	/**
	 * Load Validator class for current controller.
	 */
	private function load_validator() {
		// Load validator for controller.
		LM_API_Helper::load_controller_validator( $this->controller, $this->api_ver );
	}

	/**
	 * Return request params based on HTTP request method.
	 * @return array
	 */
	private function setup_body() {
		$body = [];
		switch ( $this->request_method ) {
			case 'get':
			case 'post':
				$body = $_REQUEST;
				break;

			case 'put':
			case 'delete':
				$body = array_merge( $_GET, LM_API_Helper::get_body_content() );
				break;

			default:
				$body = $_REQUEST;
				break;
		}

		// Remove WP q param. We dont need it.
		if ( isset( $body['q'] ) ) {
			unset( $body['q'] );
		}

		return $body;
	}

	/**
	 * Format action based on request method.
	 * So action with GET will be get_someaction
	 * @param string $action
	 * @return string
	 */
	private function resolve_action( $action ) {
		return sprintf( '%s_%s', strtolower( $_SERVER['REQUEST_METHOD'] ), $action );
	}

	/**
	 * Setup class properties and resolve correct
	 * action based on request_method
	 * If request_method = GET we must add prefix get_
	 * to action. So resolved action we be like this:
	 * get_posts, delete_posts, put_posts.
	 * @param array $request
	 */
	private function setup_object( $request ) {
		$this->controller = $request['controller'];
		$this->action = $request['action'];
		$this->api_ver = $request['api'];
		$this->request_method = strtolower( $_SERVER['REQUEST_METHOD'] );
		$this->resolved_action = $this->resolve_action( $this->action );
		$this->body = $this->setup_body();
	}

}