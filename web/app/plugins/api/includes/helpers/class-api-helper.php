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
		return file_exists(
			sprintf(
				'%s/includes/controllers/v%d/%s.php',
				LM_API_DIR,
				self::get_api_version( $api_ver ),
				$controller
			)
		);
	}

	/**
	 * Simple wrapper for include_once()
	 * @param string $controller Controller's filename
	 */
	public static function load_controller( $controller, $api_ver ) {
		include_once(
			sprintf(
				'%s/includes/controllers/v%d/%s.php',
				LM_API_DIR,
				self::get_api_version( $api_ver ),
				$controller
			)
		);
	}

	/**
	 * Load Validator for current controller.
	 * @param string $controller
	 * @param string $api_ver
	 */
	public static function load_controller_validator( $controller, $api_ver ) {
		$validator = sprintf(
			'%s/includes/validators/v%d/%s.php',
			LM_API_DIR,
			self::get_api_version( $api_ver ),
			$controller
		);

		if ( file_exists( $validator ) ) {
			include_once( $validator );
		}
	}

	/**
	 * Return API version from string.
	 * $api = v1.1.2
	 * returns float 1.1
	 * @param string $api
	 * @return float
	 */
	public static function get_api_version( $api ) {
		return floatval(
			filter_var(
				$api,
				FILTER_SANITIZE_NUMBER_FLOAT,
				FILTER_FLAG_ALLOW_FRACTION
			)
		);
	}

	/**
	 * Return robots.txt file path.
	 * @return string
	 */
	public static function get_robotstxt_path() {
		return self::get_root_path_to_file( 'robots.txt' );
	}

	/**
	 * Return .htaccess file path.
	 * @return string
	 */
	public static function get_htaccess_path() {
		return self::get_root_path_to_file( '.htaccess' );
	}

	/**
	 * Return root file path.
	 * @param string $file Filename
	 * @return string
	 */
	public static function get_root_path_to_file( $file ) {
		if ( defined( 'WEB_ROOT_PATH' ) ) {
			$file_path = WEB_ROOT_PATH . '/' . $file;
		} else {
			// Custom core folder
			// /var/www/wp.dev/web/wp/
			if ( strstr( ABSPATH, '/wp/' ) ) {
				$path = str_replace( '/wp/', '/', ABSPATH );
			} else {
				$path = ABSPATH;
			}

			$file_path = $path . $file;
		}

		return $file_path;
	}

	/**
	 * Return content for PUT/DELETE request.
	 * @return array
	 */
	public static function get_body_content() {
		// PUT request
		$dummy_content = file_get_contents( 'php://input' );
		parse_str( $dummy_content, $_request );

		return $_request;
	}

	/**
	 * Return admin's user.ID
	 * @return int
	 */
	public static function get_admin_id() {
		$user = get_user_by( 'email', get_option( 'admin_email' ) );
		// In theory user can't be empty.
		if ( $user ) {
			return $user->ID;
		} else {
			return 1; // If somthing wrong...
		}
	}

}
