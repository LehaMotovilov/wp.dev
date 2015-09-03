<?php

class Update {

	/**
	 * Update WP core.
	 * @return array
	 */
	public function post_core() {
		include_once( LM_API_DIR . '/includes/helpers/class-updater.php' );
		$updater = new LM_Update_Helper();

		if ( ! $updater->update_core() ) {
			return new WP_Error( 'error', 'Internal error.' );
		}

		return [
			'wp_version' => get_bloginfo( 'version' ),
			'wp_latest_version' => $updater->get_latest_wp_version()
		];
	}

	/**
	 * Update theme.
	 * @return array
	 */
	public function post_theme() {
		echo "<pre>";
		print_r('post_theme');
		echo "</pre>";
		exit();
	}

}
