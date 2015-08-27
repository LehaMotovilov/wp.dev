<?php

/**
 * Helper API Class.
 */
class LM_API_Helper {

	/**
	 * Simple wrapper for file_exist()
	 * @param $controller string Controller's filename
	 * @return bool
	 */
	public static function controller_exist( $controller ) {
		return file_exists( dirname( __FILE__ ) . '/controllers/' . $controller . '.php' );
	}

	/**
	 * Simple wrapper for include_once()
	 * @param @controller string Controller's filename
	 */
	public static function load_controller( $controller ) {
		include_once( dirname( __FILE__ ) . '/controllers/' . $controller . '.php' );
	}

}
