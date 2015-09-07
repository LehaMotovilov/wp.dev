<?php

/**
 * Helper API Class.
 */
class LM_API_Helper {

	/**
	 * Simple wrapper for file_exist()
	 * @param string $controller Controller's filename
	 * @param string $api_ver Api version
	 * @return bool
	 */
	public static function controller_exist( $controller, $api_ver ) {
		$api_ver = self::get_api_version( $api_ver );

		return file_exists( LM_API_DIR . '/includes/controllers/v' . $api_ver . '/' . $controller . '.php' );
	}

	/**
	 * Simple wrapper for include_once()
	 * @param string $controller Controller's filename
	 */
	public static function load_controller( $controller, $api_ver ) {
		$api_ver = self::get_api_version( $api_ver );

		include_once( LM_API_DIR . '/includes/controllers/v' . $api_ver . '/' . $controller . '.php' );
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

	/**
	 * Return robots.txt file path.
	 * @return string
	 */
	public static function get_robotstxt_path() {
		if ( defined( 'WEB_ROOT_PATH' ) ) {
			$robots_path = WEB_ROOT_PATH . '/robots.txt';
		} else {
			// Custom core folder
			// /var/www/wp.dev/web/wp/
			if ( strstr( ABSPATH, '/wp/' ) ) {
				$path = str_replace( '/wp/', '/', ABSPATH );
			} else {
				$path = ABSPATH;
			}

			$robots_path = $path . 'robots.txt';
		}

		return $robots_path;
	}

	/**
	 * Return .htaccess file path.
	 * @return string
	 */
	public static function get_htaccess_path() {
		if ( defined( 'WEB_ROOT_PATH' ) ) {
			$robots_path = WEB_ROOT_PATH . '/.htaccess';
		} else {
			// Custom core folder
			// /var/www/wp.dev/web/wp/
			if ( strstr( ABSPATH, '/wp/' ) ) {
				$path = str_replace( '/wp/', '/', ABSPATH );
			} else {
				$path = ABSPATH;
			}

			$robots_path = $path . '.htaccess';
		}

		return $robots_path;
	}

	/**
	 * Return content for PUT request.
	 * @return array
	 */
	public static function get_put_content() {
		// PUT request
		$dummy_content = file_get_contents( 'php://input' );
		parse_str( $dummy_content, $_request );

		return $_request;
	}

}
