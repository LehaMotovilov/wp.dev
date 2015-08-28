<?php

/**
 * API Class for all responses.
 */
class LM_API_Request {

	public $api_ver;
	public $controller;
	public $action;
	public $resolved_action;
	public $request_method;

	protected $_allowed_request_types = [ 'GET', 'POST', 'DELETE', 'PUT' ];

	/**
	 * Class constructor.
	 */
	public function __construct( $request ) {
		// Do some validation
		$validate = $this->validate_request( $request );
		if ( is_wp_error( $validate ) ) {
			return $validate;
		}

		// After validation we can setup class object
		$this->setup_object( $request );

		return self;
	}

	/**
	 * Validate request.
	 * @param array $request Data from $_REQUEST
	 * @return object|array WP_Error if validation failed or array if all ok.
	 */
	public function validate_request( $request ) {
		// If incorrect request?
		if ( ! $this->check_params( $request ) ) {
			return new WP_Error( 'error', 'Check API request.' );
		}

		// If incorrect API version?
		if ( ! $this->check_version( $request ) ) {
			return new WP_Error( 'error', 'Check API version.' );
		}

		// If incorrect controller?
		if ( ! $this->check_controller( $request ) ) {
			return new WP_Error( 'error', 'Check request\'s controller.' );
		}

		// If incorrect action?
		if ( ! $this->check_action( $request ) ) {
			return new WP_Error( 'error', 'Check request\'s action.' );
		}

		// If incorrect request_type?
		if ( ! $this->check_request_method() ) {
			return new WP_Error( 'error', 'Check request\'s type.' );
		}

		// Errors not found.
		return $request;
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
	}

	private function resolve_action( $action ) {
		return strtolower( $_SERVER['REQUEST_METHOD'] ) . '_' . $action;
	}

	/**
	 * First check for request.
	 * @param array $request
	 * @return bool
	 */
	private function check_params( $request ) {
		if ( ! is_array( $request ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check API version.
	 * @param array $request
	 * @return bool
	 */
	private function check_version( $request ) {
		if ( ! isset( $request['api'] ) || empty( $request['api'] ) ) {
			return false;
		}

		$ver = LM_API_Helper::get_api_version( $request['api'] );
		$api_ver = absint( LM_API_VERSION );
		if ( $ver !== $api_ver ) {
			return false;
		}

		return true;
	}

	/**
	 * Check controller.
	 * @param array $request
	 * @return bool
	 */
	private function check_controller( $request ) {
		if ( ! isset( $request['controller'] ) || empty( $request['controller'] ) ) {
			return false;
		}

		if ( ! LM_API_Helper::controller_exist( $request ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check controller's action.
	 * @param array $request
	 * @return bool
	 */
	private function check_action( $request ) {
		if ( ! isset( $request['action'] ) || empty( $request['action'] ) ) {
			return false;
		}

		LM_API_Helper::load_controller( $request );
		if ( ! method_exists( ucfirst( $request['controller'] ), $this->resolve_action( $request['action'] ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check request_method.
	 * @return bool
	 */
	private function check_request_method() {
		if ( empty( $_SERVER['REQUEST_METHOD'] ) || ! in_array( $_SERVER['REQUEST_METHOD'], $this->_allowed_request_types ) ) {
			return false;
		}

		return true;
	}

}