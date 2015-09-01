<?php

class Site {

	/**
	 * Return information about WP and theme/plugins.
	 */
	public function get_index() {
		$theme = wp_get_theme();

		return [
			'home_url' => home_url(),
			'wp_version' => get_bloginfo( 'version' ),
			'wp_latest_version' => self::get_latest_wp_version(),
			'language' => get_locale(),
			'active_theme' => [
				'name' => $theme->name,
				'version' => $theme->version,
				'path' => $theme->get_template_directory()
			],
			'active_plugins' => self::get_active_plugins()
		];
	}

	/**
	 * Return latest WP version
	 * @return string
	 */
	private static function get_latest_wp_version() {
		$latest_version = get_transient( 'latest_wp_version' );

		if ( ! $latest_version ) {
			$latest_version = 'cURL don\'t work';

			$response = wp_remote_get( 'https://api.wordpress.org/core/version-check/1.7/' );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( $response_code == 200 ) {
				$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( isset( $api_response['offers'] ) && isset( $api_response['offers'][0]['current'] ) ) {
					$latest_version = $api_response['offers'][0]['current'];
				}
			}

			// Cache for 5 days
			set_transient( 'latest_wp_version', $latest_version, DAY_IN_SECONDS * 5 );
		}

		return $latest_version;
	}

	/**
	 * Return array of active plugins.
	 * @return array
	 */
	private static function get_active_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		$dummy_active_plugins = get_option('active_plugins');
		$active_plugins = [];
		$i = 0;
		foreach ( $all_plugins as $key => $value ) {
			if ( in_array( $key , $dummy_active_plugins ) ) {
				$active_plugins[$i]['name'] = $value['Name'];
				$active_plugins[$i]['version'] = $value['Version'];
				$active_plugins[$i]['path'] = $key;
				$i++;
			}
		}
		return $active_plugins;
	}
}