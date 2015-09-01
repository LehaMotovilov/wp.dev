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
	 * @param array $request
	 * @return object
	 */
	public function __construct( $request ) {
		// After validation we can setup class object
		$this->setup_object( $request );

		return self;
	}

	/**
	 * Validate request.
	 * @return object|array WP_Error if validation failed or array if all ok.
	 */
	public function validate() {
		// If incorrect request_type?
		if ( ! $this->check_request_method() ) {
			return new WP_Error( 'error', 'Check request\'s type.' );
		}

		// If incorrect API version?
		if ( ! $this->check_version() ) {
			return new WP_Error( 'error', 'Check API version.' );
		}

		// If incorrect controller?
		if ( ! $this->check_controller() ) {
			return new WP_Error( 'error', 'Check request\'s controller.' );
		}

		// If incorrect action?
		if ( ! $this->check_action() ) {
			return new WP_Error( 'error', 'Check request\'s action.' );
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

	/**
	 * Format action based on request method.
	 * So action with GET will be get_someaction
	 * @param string $action
	 * @return string
	 */
	private function resolve_action( $action ) {
		return strtolower( $_SERVER['REQUEST_METHOD'] ) . '_' . $action;
	}

	/**
	 * Check API version.
	 * @param array $request
	 * @return bool
	 */
	private function check_version() {
		if ( ! isset( $this->api_ver ) || empty( $this->api_ver ) ) {
			return false;
		}

		$ver = LM_API_Helper::get_api_version( $this->api_ver );
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
	private function check_controller() {
		if ( ! isset( $this->controller ) || empty( $this->controller ) ) {
			return false;
		}

		if ( ! LM_API_Helper::controller_exist( $this->controller, $this->api_ver ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check controller's action exist and public.
	 * @param array $request
	 * @return bool
	 */
	private function check_action() {
		if ( ! isset( $this->resolved_action ) || empty( $this->resolved_action ) ) {
			return false;
		}

		// Check is method exist.
		LM_API_Helper::load_controller( $this->controller, $this->api_ver );
		if ( ! method_exists( ucfirst( $this->controller ), $this->resolved_action ) ) {
			return false;
		}

		// Check is method is public.
		$reflection = new ReflectionMethod( ucfirst( $this->controller ), $this->resolved_action );
		if ( ! $reflection->isPublic() ) {
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