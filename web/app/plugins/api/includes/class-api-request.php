<?php

/**
 * API Class for all responses.
 */
class LM_API_Request {

	public static $_allowed_request_types = [ 'GET', 'POST' ];

	/**
	 * Validate request.
	 */
	public static function validate_request( $request ) {
		// If incorrect request?
		if ( ! self::check_params( $request ) ) {
			return new WP_Error( 'error', 'Check API request.' );
		}

		// If incorrect API version?
		if ( ! self::check_version( $request ) ) {
			return new WP_Error( 'error', 'Check API version.' );
		}

		// If incorrect controller?
		if ( ! self::check_controller( $request ) ) {
			return new WP_Error( 'error', 'Check request\'s controller.' );
		}

		// If incorrect action?
		if ( ! self::check_action( $request ) ) {
			return new WP_Error( 'error', 'Check request\'s action.' );
		}

		// If incorrect request_type?
		if ( ! self::check_request_method() ) {
			return new WP_Error( 'error', 'Check request\'s type.' );
		}

		// Errors not found.
		return $request;
	}

	/**
	 * First check for request.
	 * @param array $request
	 * @return bool
	 */
	private static function check_params( $request ) {
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
	private static function check_version( $request ) {
		if ( ! isset( $request['api'] ) || empty( $request['api'] ) ) {
			return false;
		}

		$ver = absint( filter_var( $request['api'], FILTER_SANITIZE_NUMBER_INT ) );
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
	private static function check_controller( $request ) {
		if ( ! isset( $request['controller'] ) || empty( $request['controller'] ) ) {
			return false;
		}

		if ( ! LM_API_Helper::controller_exist( $request['controller'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check controller's action.
	 * @param array $request
	 * @return bool
	 */
	private static function check_action( $request ) {
		if ( ! isset( $request['action'] ) || empty( $request['action'] ) ) {
			return false;
		}

		LM_API_Helper::load_controller( $request['controller'] );
		if ( ! method_exists( ucfirst( $request['controller'] ), $request['action'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check request_method.
	 * @return bool
	 */
	private static function check_request_method() {
		if ( empty( $_SERVER['REQUEST_METHOD'] ) || ! in_array( $_SERVER['REQUEST_METHOD'], self::$_allowed_request_types ) ) {
			return false;
		}

		return true;
	}

}