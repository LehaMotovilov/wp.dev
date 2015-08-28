<?php

/**
 * Helper API Class.
 */
class LM_API_Helper {

	/**
	 * Simple wrapper for file_exist()
	 * @param string $controller Controller's filename
	 * @return bool
	 */
	public static function controller_exist( $request ) {
		$api_ver = self::get_api_version( $request['api'] );

		return file_exists( dirname( __FILE__ ) . '/controllers/v' . $api_ver . '/' . $request['controller'] . '.php' );
	}

	/**
	 * Simple wrapper for include_once()
	 * @param string $controller Controller's filename
	 */
	public static function load_controller( $request ) {
		$api_ver = self::get_api_version( $request['api'] );

		include_once( dirname( __FILE__ ) . '/controllers/v' . $api_ver . '/' . $request['controller'] . '.php' );
	}

	/**
	 * Return API version from string.
	 * $api = v1.1
	 * returns int 1
	 * @param string $api
	 * @return int
	 */
	public static function get_api_version( $api ) {
		return absint( filter_var( $api, FILTER_SANITIZE_NUMBER_INT ) );
	}

}
