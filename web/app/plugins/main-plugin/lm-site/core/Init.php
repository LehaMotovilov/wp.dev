<?php
namespace LM\Site;

/**
 * Main Core Class
 */
class Init {

	function run() {
		// Define plugin constants.
		$this->define();

		// Add plugin hooks.
		$this->core_plugin_hooks();

		// Load language text domain.
		$this->load_language();
	}


	/**
	 * Define framework constants.
	 */
	private function define() {
		define( 'LM_PLUGIN_BASE_DIR', __DIR__ );
		define( 'LM_PLUGIN_BASE_URL', plugins_url( 'main-plugin' ) );
	}

	/**
	 * Init all WP plugin hooks.
	 */
	private function core_plugin_hooks() {
		// Deactivate Main Plugin
		register_deactivation_hook( WP_PLUGIN_DIR . '/main-plugin/main-plugin.php', array( $this, 'plugin_deactivate' ) );
	}

	/**
	 * Translate framework.
	 */
	private function load_language() {
		add_action( 'init', function(){
			load_plugin_textdomain( 'main-plugin', false, 'main-plugin/lm-site/languages' );
		} );
	}

	/**
	 * Plugin deactivation hook.
	 */
	public function plugin_deactivate() {
		$url = admin_url( 'plugins.php' );
		wp_die( 'Oops! You can\'t deactivate main plugin. <a href="' . $url . '" title="Return to plugins">Return to plugins.</a>' );
	}

}