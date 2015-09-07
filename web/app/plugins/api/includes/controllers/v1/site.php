<?php

class Site {

	/**
	 * Return information about WP and theme/plugins.
	 * @return array
	 */
	public function get_index() {
		include_once( LM_API_DIR . '/includes/helpers/class-updater.php' );
		$updater = new LM_Update_Helper();
		$theme = wp_get_theme();

		return [
			'home_url' => home_url(),
			'wp_version' => get_bloginfo( 'version' ),
			'wp_latest_version' => $updater->get_latest_wp_version(),
			'language' => get_locale(),
			'active_theme' => [
				'name' => $theme->name,
				'version' => $theme->version,
				'path' => $theme->get_template_directory()
			],
			'active_plugins' => $updater->get_active_plugins()
		];
	}

	/**
	 * Return all options or one option from request.
	 * @return array
	 */
	public function get_options() {
		if ( isset( $_GET['option'] ) && !empty( $_GET['option'] ) ) {
			$key = sanitize_text_field( $_GET['option'] );
			$return[$key] = get_option( $key );
		} else {
			// Else return all options with autoload param.
			$return['alloptions'] = wp_load_alloptions();
		}

		return $return;
	}

	/**
	 * Update single option.
	 * @return array
	 */
	public function put_options() {
		// PUT method not allow get data from $_REQUEST
		$_request = LM_API_Helper::get_put_content();

		// If empty key
		if ( ! isset( $_request['option'] ) || empty( $_request['option'] ) ) {
			return new WP_Error( '400', 'You forget about option param.' );
		}

		// If empty value
		if ( ! isset( $_request['value'] ) || empty( $_request['value'] ) ) {
			return new WP_Error( '400', 'You forget about value param.' );
		}

		$key = sanitize_text_field( $_request['option'] );
		$value = trim( $_request['value'] );

		if ( update_option( $key, $value ) ) {
			return [ $key => $value ];
		} else {
			return new WP_Error( '500', 'Update failed, maybe "' . $key . '" is already "' . $value . '"?' );
		}
	}

	/**
	 * Return robots.txt content.
	 * @return array
	 */
	public function get_robots() {
		return $this->get_robots_txt_content();
	}

	/**
	 * Update robots.txt content.
	 * @return array
	 */
	public function put_robots() {
		// PUT method not allow get data from $_REQUEST
		$_request = LM_API_Helper::get_put_content();
		// If empty content
		if ( ! isset( $_request['content'] ) || empty( $_request['content'] ) ) {
			return new WP_Error( '400', 'You forget about content param.' );
		}

		$content = stripcslashes( $_request['content'] );
		$content = str_replace( ["\\n", "\n", "\r", "\r\n", "\n\r"], PHP_EOL, $content );

		$robots_file = LM_API_Helper::get_robotstxt_path();

		// Get wp_filesystem class
		$wp_filesystem = $this->get_wp_filesystem();

		if ( $wp_filesystem->put_contents( $robots_file, $content, 644 ) ) {
			return $this->get_robots_txt_content();
		}

		return new WP_Error( '500', 'Cannot update robots.txt, check file permissions.' );
	}

	/**
	 * Return .htaccess content.
	 * @return array
	 */
	public function get_htaccess() {
		return $this->get_htaccess_content();
	}

	/**
	 * Update .htaccess content.
	 * @return array
	 */
	public function put_htaccess() {
		// PUT method not allow get data from $_REQUEST
		$_request = LM_API_Helper::get_put_content();

		// If empty content
		if ( ! isset( $_request['content'] ) || empty( $_request['content'] ) ) {
			return new WP_Error( '400', 'You forget about content param.' );
		}

		if ( ! function_exists( 'insert_with_markers' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/misc.php' );
		}

		$content = $_request['content'];

		// Use marker for cool formating.
		// See https://developer.wordpress.org/reference/functions/insert_with_markers/
		if ( isset( $_request['marker'] ) && !empty( $_request['marker'] ) ) {
			$marker = $_request['marker'];
		} else {
			$marker = 'FROM_API';
		}

		$htaccess_file = LM_API_Helper::get_htaccess_path();

		if ( insert_with_markers( $htaccess_file, $marker, [ $content ] ) ) {
			return $this->get_htaccess_content();
		} else {
			return new WP_Error( '500', 'Cannot update .htaccess, check file permissions.' );
		}
	}

	/**
	 * Return robots.txt content.
	 * @return array
	 */
	private function get_robots_txt_content() {
		$robots_file = LM_API_Helper::get_robotstxt_path();

		// Get wp_filesystem class
		$wp_filesystem = $this->get_wp_filesystem();

		if ( $wp_filesystem->exists( $robots_file ) ) {
			$out = $wp_filesystem->get_contents( $robots_file );
		} else {
			// It's very strange situation.
			return new WP_Error( '500', 'robots.txt not found.' );
		}

		// Make array from string.
		$robots_content = array_filter( explode( PHP_EOL, $out) );

		return [
			'robots_content' => $out,
			'robots_content_array' => $robots_content
		];
	}

	/**
	 * Return htaccess content.
	 * @return array
	 */
	private function get_htaccess_content() {
		$htaccess_file = LM_API_Helper::get_htaccess_path();

		// Get wp_filesystem class
		$wp_filesystem = $this->get_wp_filesystem();

		if ( ! $wp_filesystem->exists( $htaccess_file ) ) {
			return new WP_Error( '500', '.htaccess not found.' );
		}

		$out = $wp_filesystem->get_contents( $htaccess_file );

		// Make array from string.
		$htaccess_content = array_filter( explode( PHP_EOL, $out) );

		return [
			'htaccess_content' => $out,
			'htaccess_content_array' => $htaccess_content
		];
	}

	/**
	 * Return WP_Filesystem object.
	 * @return object
	 */
	private function get_wp_filesystem() {
		// Get wp_filesystem class
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			WP_Filesystem(); // Yeap its function :-)
		}

		global $wp_filesystem;

		return $wp_filesystem;
	}

}