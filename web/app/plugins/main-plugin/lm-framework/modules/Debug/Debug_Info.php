<?php
namespace LM\Modules\Debug;

use Imagick;
use mysqli;

class Debug_Info {

	public function get_wordpress_info() {

		$wordpress_info = array(
			array(
				'key' => 'home_url',
				'title' => __( 'WP Home', 'lm-framework' ),
				'value' => home_url()
			),
			array(
				'key' => 'version',
				'title' => __( 'WP Current Version', 'lm-framework' ),
				'value' => get_bloginfo( 'version' )
			),
			array(
				'key' => 'latest_version',
				'title' => __( 'WP Latest Version', 'lm-framework' ),
				'value' => $this->get_latest_wp_version()
			),
			array(
				'key' => 'memory_limit',
				'title' => __( 'WP Memory Limit', 'lm-framework' ),
				'value' => defined( 'WP_MEMORY_LIMIT' )  ? WP_MEMORY_LIMIT : '---'
			),
			array(
				'key' => 'locale',
				'title' => __( 'WP Locale', 'lm-framework' ),
				'value' => get_locale()
			),
			array(
				'key' => 'debug',
				'title' => __( 'WP Debug', 'lm-framework' ),
				'value' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'On' : 'Off'
			),
			array(
				'key' => 'local_dev',
				'title' => __( 'WP Local Dev', 'lm-framework' ),
				'value' => ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) ? 'On' : 'Off'
			),
			array(
				'key' => 'update_core',
				'title' => __( 'WP Automatic Updater', 'lm-framework' ),
				'value' => ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) ? 'Off' : 'On'
			),
			array(
				'key' => 'max_upload_size',
				'title' => __( 'WP Max Upload Size', 'lm-framework' ),
				'value' => size_format( wp_max_upload_size() )
			)
		);

		return $wordpress_info;
	}

	public function get_php_info() {

		$php_info = array(
			array(
				'key' => 'php_version',
				'title' => __( 'PHP Version', 'lm-framework' ),
				'value' => phpversion()
			),
			array(
				'key' => 'server',
				'title' => __( 'Server', 'lm-framework' ),
				'value' => $_SERVER['SERVER_SOFTWARE']
			),
			array(
				'key' => 'display_errors',
				'title' => __( 'Display Errors', 'lm-framework' ),
				'value' => ( ini_get('display_errors') ) ? 'On' : 'Off'
			),
			array(
				'key' => 'upload_max_filesize',
				'title' => __( 'Max File Size', 'lm-framework' ),
				'value' => ini_get('upload_max_filesize')
			),
			array(
				'key' => 'post_max_size',
				'title' => __( 'Post Max Size', 'lm-framework' ),
				'value' => ini_get('post_max_size')
			),
			array(
				'key' => 'max_file_uploads',
				'title' => __( 'Max File Uploads', 'lm-framework' ),
				'value' => ini_get('max_file_uploads')
			),
			array(
				'key' => 'http_host',
				'title' => __( 'HTTP Host', 'lm-framework' ),
				'value' => $_SERVER['HTTP_HOST']
			),
			array(
				'key' => 'http_connection',
				'title' => __( 'HTTP Connection', 'lm-framework' ),
				'value' => $_SERVER['HTTP_CONNECTION']
			),
			array(
				'key' => 'server_protocol',
				'title' => __( 'Server Protocol', 'lm-framework' ),
				'value' => $_SERVER['SERVER_PROTOCOL']
			),
		);

		return $php_info;
	}

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

		$images_info = array(
			array(
				'key' => 'imagick',
				'title' => __( 'ImageMagick', 'lm-framework' ),
				'value' => $imagick
			),
			array(
				'key' => 'imagick_version',
				'title' => __( 'ImageMagick Version', 'lm-framework' ),
				'value' => $imagick_version
			),
			array(
				'key' => 'gd',
				'title' => __( 'GD', 'lm-framework' ),
				'value' => $gd
			),
			array(
				'key' => 'gd_version',
				'title' => __( 'GD Version', 'lm-framework' ),
				'value' => $gd_version
			),
			array(
				'key' => 'upload_dir',
				'title' => __( 'WP Uploads Dir', 'lm-framework' ),
				'value' => wp_upload_dir()
			),

		);

		return $images_info;
	}

	public function get_mysql_info() {

		$info = array();

		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD );
		if ( !mysqli_connect_errno() ) {
			$info['version'] = $mysqli->server_info;
		}
		$mysqli->close();

		global $wpdb;

		$mysql_info = array(
			array(
				'key' => 'mysql_version',
				'title' => __( 'MySQL Version', 'lm-framework' ),
				'value' => $info['version']
			),
			array(
				'key' => 'key_buffer_size',
				'title' => __( 'key_buffer_size', 'lm-framework' ),
				'desc' => __( 'Key cache size limit', 'lm-framework' ),
				'value' => size_format( $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='key_buffer_size';" ) )
			),
			array(
				'key' => 'max_allowed_packet',
				'title' => __( 'max_allowed_packet', 'lm-framework' ),
				'desc' => __( 'Individual query size limit', 'lm-framework' ),
				'value' => size_format( $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='max_allowed_packet';" ) )
			),
			array(
				'key' => 'max_connections',
				'title' => __( 'max_connections', 'lm-framework' ),
				'desc' => __( 'Max number of client connections', 'lm-framework' ),
				'value' => $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='max_connections';" )
			),
			array(
				'key' => 'query_cache_limit',
				'title' => __( 'query_cache_limit', 'lm-framework' ),
				'desc' => __( 'Individual query cache size limit', 'lm-framework' ),
				'value' => size_format( $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='query_cache_limit';" ) )
			),
			array(
				'key' => 'query_cache_size',
				'title' => __( 'query_cache_size', 'lm-framework' ),
				'desc' => __( 'Total cache size limit', 'lm-framework' ),
				'value' => size_format( $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='query_cache_size';" ) )
			),
			array(
				'key' => 'query_cache_type',
				'title' => __( 'query_cache_type', 'lm-framework' ),
				'desc' => __( 'Query cache on or off', 'lm-framework' ),
				'value' => $wpdb->get_var( "SELECT `VARIABLE_VALUE` FROM INFORMATION_SCHEMA.GLOBAL_VARIABLES WHERE VARIABLE_NAME='query_cache_type';" )
			)
		);

		return $mysql_info;
	}

	public function get_emails_info() {
		global $wp_filter;

		$email_info = array(
			array(
				'key' => 'admin_email',
				'title' => __( 'Admin Email', 'lm-framework' ),
				'value' => get_bloginfo( 'admin_email' )
			),
			array(
				'key' => 'filter_mail_from',
				'title' => __( 'Filter: wp_mail_from', 'lm-framework' ),
				'value' => isset( $wp_filter['wp_mail_from'] )  ? $wp_filter['wp_mail_from'] : '---'
			),
			array(
				'key' => 'filter_mail_from_name',
				'title' => __( 'Filter: wp_mail_from_name', 'lm-framework' ),
				'value' => isset( $wp_filter['wp_mail_from_name'] )  ? $wp_filter['wp_mail_from_name'] : '---'
			)
		);

		return $email_info;
	}

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
