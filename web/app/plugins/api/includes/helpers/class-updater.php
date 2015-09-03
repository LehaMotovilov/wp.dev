<?php

class LM_Update_Helper {

	/**
	 * Update WP core.
	 * @todo Realize this method.
	 */
	public function update_core() {
		return true;
	}

	/**
	 * Update single plugin.
	 * @todo Realize this method.
	 */
	public function update_plugin() {
		return false;
	}

	/**
	 * Update single theme.
	 * @todo Realize this method.
	 */
	public function update_theme() {
		$this->get_active_theme();
		return false;
	}

	/**
	 * Return latest WP version
	 * @return string
	 */
	public function get_latest_wp_version() {
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
	public function get_active_plugins() {
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

	/**
	 * Return info about currect active theme.
	 * @return array
	 */
	public function get_active_theme() {
		$theme = wp_get_theme();
		echo "<pre>";
		print_r($theme);
		echo "</pre>";
		exit();
		return $active_plugins;
	}

}