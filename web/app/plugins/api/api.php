<?php
/**
 * Plugin Name: API
 * Plugin URI:
 * Description: Simple API
 * Version: 1.0.0
 * Author: LehaMotovilov
 * Author URI: http://lehamotovilov.com/
 *
 * Yeap! I know about WP REST API (WP API). :-)
 * But I need my own API...
 *
 * Rest Methods
 *  - POST		Create
 *  - GET		Read
 *  - PUT	 	Update
 *  - DELETE	Delete
 *
 * Examples:
 * 1. GET http://wp.dev/api/v1/posts/
 * 		will be used /controllers/v1/posts/ action get_index
 * 2. POST http://wp.dev/api/v2/posts/test/
 * 		will be used /controllers/v2/posts/ action post_test
 */

// Block direct requests.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Version number for our API.
define( 'LM_API_VERSION', '1.0.0' );

// Include our main Class.
require_once( dirname( __FILE__ ) . '/includes/class-api-main.php' );

// We take care about activation/deactivation.
register_activation_hook( __FILE__, [ 'LM_API_Main', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'LM_API_Main', 'deactivate' ] );

// Lets our API working.
$api = new LM_API_Main();
$api->start();
