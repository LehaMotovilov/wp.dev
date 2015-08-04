<?php
namespace LM\Modules\Debug;

use Imagick;
use mysqli;

/**
 * Helper Class for Debug module.
 *
 * @author LehaMotovilov <lehaqs@gmail.com>
 * @version 1.0
 */
class Debug_Info {

	/**
	 * Return info about WordPress
	 * @return array
	 */
	public function get_wordpress_info() {

		$wordpress_info = [
			[
				'key' => 'home_url',
				'title' => __( 'WP Home', 'lm-framework' ),
				'value' => home_url()
			],
			[
				'key' => 'version',
				'title' => __( 'WP Current Version', 'lm-framework' ),
				'value' => get_bloginfo( 'version' )
			],
			[
				'key' => 'latest_version',
				'title' => __( 'WP Latest Version', 'lm-framework' ),
				'value' => $this->get_latest_wp_version()
			],
			[
				'key' => 'memory_limit',
				'title' => __( 'WP Memory Limit', 'lm-framework' ),
				'value' => defined( 'WP_MEMORY_LIMIT' )  ? WP_MEMORY_LIMIT : '---'
			],
			[
				'key' => 'locale',
				'title' => __( 'WP Locale', 'lm-framework' ),
				'value' => get_locale()
			],
			[
				'key' => 'debug',
				'title' => __( 'WP Debug', 'lm-framework' ),
				'value' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'On' : 'Off'
			],
			[
				'key' => 'local_dev',
				'title' => __( 'WP Local Dev', 'lm-framework' ),
				'value' => ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) ? 'On' : 'Off'
			],
			[
				'key' => 'update_core',
				'title' => __( 'WP Automatic Updater', 'lm-framework' ),
				'value' => ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) ? 'Off' : 'On'
			],
			[
				'key' => 'max_upload_size',
				'title' => __( 'WP Max Upload Size', 'lm-framework' ),
				'value' => size_format( wp_max_upload_size() )
			]
		];

		return $wordpress_info;
	}

	/**
	 * Return info about PHP
	 * @return array
	 */
	public function get_php_info() {

		$php_info = [
			[
				'key' => 'php_version',
				'title' => __( 'PHP Version', 'lm-framework' ),
				'value' => phpversion()
			],
			[
				'key' => 'server',
				'title' => __( 'Server', 'lm-framework' ),
				'value' => $_SERVER['SERVER_SOFTWARE']
			],
			[
				'key' => 'display_errors',
				'title' => __( 'Display Errors', 'lm-framework' ),
				'value' => ( ini_get('display_errors') ) ? 'On' : 'Off'
			],
			[
				'key' => 'upload_max_filesize',
				'title' => __( 'Max File Size', 'lm-framework' ),
				'value' => ini_get('upload_max_filesize')
			],
			[
				'key' => 'post_max_size',
				'title' => __( 'Post Max Size', 'lm-framework' ),
				'value' => ini_get('post_max_size')
			],
			[
				'key' => 'max_file_uploads',
				'title' => __( 'Max File Uploads', 'lm-framework' ),
				'value' => ini_get('max_file_uploads')
			],
			[
				'key' => 'http_host',
				'title' => __( 'HTTP Host', 'lm-framework' ),
				'value' => $_SERVER['HTTP_HOST']
			],
			[
				'key' => 'http_connection',
				'title' => __( 'HTTP Connection', 'lm-framework' ),
				'value' => $_SERVER['HTTP_CONNECTION']
			],
			[
				'key' => 'server_protocol',
				'title' => __( 'Server Protocol', 'lm-framework' ),
				'value' => $_SERVER['SERVER_PROTOCOL']
			]
		];

		return $php_info;
	}

	/**
	 * Return info about Image Classes (GD and Imagick)
	 * @return array
	 */
	public function get_images_info() {

		if ( extension_loaded('imagick') && class_exists("Imagick") ) {
			$imagick = __( 'Installed', 'lm-framework' );

			$imagick_class = new Imagick();
			$version = $imagick_class->getVersion();
			if ( is_array( $version ) && isset( $version['versionString'] ) ) {
				$imagick_version = $version['versionString'];
			}
		} else {
			$imagick = '---';
			$imagick_version = '---';
		}

		if ( extension_loaded('gd') && function_exists('gd_info') ) {
			$gd = __( 'Installed', 'lm-framework' );
			$version = gd_info();
			if ( is_array( $version ) && isset( $version['GD Version'] ) ) {
				$gd_version = $version['GD Version'];
			}
		} else {
			$gd = '---';
			$gd_version = '---';
		}

		$images_info = [
			[
				'key' => 'imagick',
				'title' => __( 'ImageMagick', 'lm-framework' ),
				'value' => $imagick
			],
			[
				'key' => 'imagick_version',
				'title' => __( 'ImageMagick Version', 'lm-framework' ),
				'value' => $imagick_version
			],
			[
				'key' => 'gd',
				'title' => __( 'GD', 'lm-framework' ),
				'value' => $gd
			],
			[
				'key' => 'gd_version',
				'title' => __( 'GD Version', 'lm-framework' ),
				'value' => $gd_version
			],
			[
				'key' => 'upload_dir',
				'title' => __( 'WP Uploads Dir', 'lm-framework' ),
				'value' => wp_upload_dir()
			]
		];

		return $images_info;
	}

	/**
	 * Return info about MySQL
	 * @return array
	 */
	public function get_mysql_info() {

		global $wpdb;
		$info = [];

		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD );
		if ( !mysqli_connect_errno() ) {
			$info['version'] = $mysqli->server_info;
		}
		$mysqli->close();


		$mysql_info = [
			[
				'key' => 'mysql_version',
				'title' => __( 'MySQL Version', 'lm-framework' ),
				'value' => $info['version']
			],
			[
				'key' => 'key_buffer_size',
				'title' => __( 'key_buffer_size', 'lm-framework' ),
				'desc' => __( 'Key cache size limit', 'lm-framework' ),
				'value' => size_format( $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='key_buffer_size';" ) )
			],
			[
				'key' => 'max_allowed_packet',
				'title' => __( 'max_allowed_packet', 'lm-framework' ),
				'desc' => __( 'Individual query size limit', 'lm-framework' ),
				'value' => size_format( $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='max_allowed_packet';" ) )
			],
			[
				'key' => 'max_connections',
				'title' => __( 'max_connections', 'lm-framework' ),
				'desc' => __( 'Max number of client connections', 'lm-framework' ),
				'value' => $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='max_connections';" )
			],
			[
				'key' => 'query_cache_limit',
				'title' => __( 'query_cache_limit', 'lm-framework' ),
				'desc' => __( 'Individual query cache size limit', 'lm-framework' ),
				'value' => size_format( $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='query_cache_limit';" ) )
			],
			[
				'key' => 'query_cache_size',
				'title' => __( 'query_cache_size', 'lm-framework' ),
				'desc' => __( 'Total cache size limit', 'lm-framework' ),
				'value' => size_format( $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='query_cache_size';" ) )
			],
			[
				'key' => 'query_cache_type',
				'title' => __( 'query_cache_type', 'lm-framework' ),
				'desc' => __( 'Query cache on or off', 'lm-framework' ),
				'value' => $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='query_cache_type';" )
			]
		];

		return $mysql_info;
	}

	/**
	 * Return info about emails in WP system
	 * @return array
	 */
	public function get_emails_info() {
		global $wp_filter;

		$email_info = [
			[
				'key' => 'admin_email',
				'title' => __( 'Admin Email', 'lm-framework' ),
				'value' => get_bloginfo( 'admin_email' )
			],
			[
				'key' => 'filter_mail_from',
				'title' => __( 'Filter: wp_mail_from', 'lm-framework' ),
				'value' => isset( $wp_filter['wp_mail_from'] )  ? $wp_filter['wp_mail_from'] : '---'
			],
			[
				'key' => 'filter_mail_from_name',
				'title' => __( 'Filter: wp_mail_from_name', 'lm-framework' ),
				'value' => isset( $wp_filter['wp_mail_from_name'] )  ? $wp_filter['wp_mail_from_name'] : '---'
			]
		];

		return $email_info;
	}

	/**
	 * Return latest WP version
	 * @return string
	 */
	private function get_latest_wp_version() {
		$latest_version = get_transient( 'latest_wp_version' );

		if ( ! $latest_version ) {
			$latest_version = 'cURL don\'t work, so visit here: <a href="https://wordpress.org/download/release-archive/" target="_blank">Check Version</a>';

			$response = wp_remote_get( 'https://api.wordpress.org/core/version-check/1.7/' );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( $response_code == 200 ) {
				$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( isset( $api_response['offers'] ) && isset( $api_response['offers'][0]['current'] ) ) {
					$latest_version = $api_response['offers'][0]['current'];
				}
			}

			set_transient( 'latest_wp_version', $latest_version, DAY_IN_SECONDS );
		}

		return $latest_version;
	}

}
