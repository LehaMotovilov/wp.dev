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
		// If empty key
		if ( ! isset( $_REQUEST['option'] ) || empty( $_REQUEST['option'] ) ) {
			return new WP_Error( 'error', 'You forget about option param.' );
		}

		// If empty value
		if ( ! isset( $_REQUEST['value'] ) || empty( $_REQUEST['value'] ) ) {
			return new WP_Error( 'error', 'You forget about value param.' );
		}

		$key = sanitize_text_field( $_REQUEST['option'] );
		$value = trim( $_REQUEST['value'] );

		if ( update_option( $key, $value ) ) {
			return [ $key => $value ];
		} else {
			return new WP_Error( 'error', 'Update failed, maybe "' . $key . '" is already "' . $value . '"?' );
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
		// If empty content
		if ( ! isset( $_REQUEST['content'] ) || empty( $_REQUEST['content'] ) ) {
			return new WP_Error( 'error', 'You forget about content param.' );
		}

		$content = stripcslashes( $_REQUEST['content'] );
		$content = str_replace( ["\\n", "\n", "\r", "\r\n", "\n\r"], PHP_EOL, $content );

		// Try write new content
		$robots_file = LM_API_Helper::get_real_robotstxt();
		if ( file_put_contents( $robots_file, $content ) ) {
			return $this->get_robots_txt_content();
		}

		// If cannot write to robots.txt
		// try fallback action...
		// mu-plugin with filter 'robots_txt'
		if ( file_exists( WPMU_PLUGIN_DIR . '/robots_txt.php' ) ) {
			// this option used in robots_txt.php plugin
			update_option( 'robots_txt_content_rewrited', $content, $autoload = 'no' );

			return $this->get_robots_txt_content();
		}

		return new WP_Error( 'error', 'Cannot update robots.txt.' );
	}

	/**
	 * Return robots.txt content.
	 * @return array
	 */
	private function get_robots_txt_content() {
		// Check real file
		$robots_file = LM_API_Helper::get_real_robotstxt();

		// If file exist get content from real file
		// else get content from WordPress API.
		if ( file_exists( $robots_file ) ) {
			$out = file_get_contents( $robots_file );
		} else {
			ob_start();
			do_robots(); // echo robots.txt content
			$out = ob_get_contents();
			ob_end_clean();
		}

		// It's very strange situation.
		if ( empty( $out ) ) {
			return new WP_Error( 'error', 'robots.txt not found.' );
		}

		// Make array from string.
		$robots_content = array_filter( explode( PHP_EOL, $out) );

		return [
			'robots_content' => $out,
			'robots_content_array' => $robots_content
		];
	}

}