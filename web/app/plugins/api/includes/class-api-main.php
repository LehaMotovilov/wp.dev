<?php

/**
 * Main API Class.
 */
class LM_API_Main {

	/**
	 * It's main start function.
	 * We need to add all rewrites and process API requests.
	 */
	public function start() {
		add_action( 'init', [ $this, 'rewrite_rules' ] );
		add_action( 'template_redirect', [ $this, 'api_request_handler' ] );
	}

	/**
	 * Plugin activation hook.
	 */
	public function activate() {
		add_option( 'api_flush_rewrite_rules', 'yes', $deprecated = '', $autoload = 'yes' );
		update_option( 'api_flush_rewrite_rules', 'yes' );
	}

	/**
	 * Plugin deactivation hook.
	 * We need to clear after plugin deactivation.
	 */
	public function deactivate() {
		flush_rewrite_rules( false );
	}

	/**
	 * Initial rewrite rules.
	 */
	public function rewrite_rules() {
		// Remove controller and action from url.
		add_rewrite_tag( '%controller%', '([^&]+)' );
		add_rewrite_tag( '%action%', '([^&]+)' );

		// Rewrite for Controller + Action.
		add_rewrite_rule(
			'^api\/(v\d)\/(\w+)\/(\w+)\/?',
			'index.php?api=$matches[1]&controller=$matches[2]&action=$matches[3]',
			'top'
		);

		// Rewrite just for Controller.
		add_rewrite_rule(
			'^api\/(v\d)\/(\w+)\/?',
			'index.php?api=$matches[1]&controller=$matches[2]&action=index',
			'top'
		);

		// Rewrite just for api/v1/ show information about API.
		add_rewrite_rule(
			'^api\/(v\d)\/?',
			'index.php?api=$matches[1]&controller=info&action=index',
			'top'
		);

		global $wp;
		$wp->add_query_var( 'api' );
		$wp->add_query_var( 'controller' );
		$wp->add_query_var( 'action' );

		// We need to apply custom rewrites but...
		// flush_rewrite_rules() don't work correctly on register_activation_hook()
		// so I deside made this additional check.
		if ( get_option( 'api_flush_rewrite_rules' ) == 'yes' ) {
			flush_rewrite_rules( false );
			update_option( 'api_flush_rewrite_rules', 'no' );
		}
	}

	/**
	 * Run request handler for API.
	 */
	public function api_request_handler() {
		// Check if it's API request
		if ( empty( $GLOBALS['wp']->query_vars['api'] ) ) {
			return;
		}

		// Let's parse request and launch API methods.
		$this->process_request( $GLOBALS['wp']->query_vars );
	}

	/**
	 * Main API method, we must:
	 * - validate request
	 * - run correct action from needed controller
	 * - return beauty response
	 * @param array $request Array with request allowed vars.
	 */
	public function process_request( $request ) {
		include_once( dirname( __FILE__ ) . '/class-api-helper.php' );
		include_once( dirname( __FILE__ ) . '/class-api-request.php' );
		include_once( dirname( __FILE__ ) . '/class-api-response.php' );

		// Create new request object with validation on object creation.
		$request = new LM_API_Request( $request );
		if ( ! is_wp_error( $request ) ) {
			$response['status'] = '';
			$response['data'] = $this->run_action( $request );
		} else {
			$response = [
				'status' => 'error',
				'error_message' => $request->get_error_message()
			];
		}

		LM_API_Response::response( $response );
	}

	/**
	 * Run dynamically action from controller.
	 * @param object $request
	 * @return array|object Array of results or WP_Error object
	 */
	private function run_action( $request ) {
		return call_user_func( [ ucfirst( $request->controller ), $request->resolved_action ] );
	}

}
