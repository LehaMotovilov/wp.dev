<?php
/**
 *	Plugin Name: Change upload dir.
 *	Plugin URI:
 *	Description: Change uploads from wp-content/uploads to /uploads.
 *	Version: 1.2.0
 *	Author: LehaMotovilov
 *	Author URI: http://lehaqs.wordpress.com/
 */

add_filter( 'upload_dir', function( $upload_dir ) {
	return $upload_dir;
	// Check if we forgot define uploads path in wp-config.php
	if ( !defined( 'WP_UPLOADS_DIR' ) || !defined( 'WP_UPLOADS_URL' ) ) {
		return $upload_dir;
	}

	/**
	 * WordPress Multisite
	 * 1 - blog ID
	 * 2 - blog ID
	 * /var/www/html/uploads/1/2014/02/
	 * /var/www/html/uploads/2/2014/02/
	 * -------------------------------
	 * WordPress usual install
	 * /var/www/html/uploads/2014/02/
	 */
	if ( is_multisite() ) {
		$blog_id = get_current_blog_id();
		$upload_dir['path']    	= WP_UPLOADS_DIR . '/' . $blog_id . $upload_dir['subdir'];
		$upload_dir['basedir'] 	= WP_UPLOADS_DIR . '/' . $blog_id;
		$upload_dir['baseurl'] 	= WP_UPLOADS_URL . $blog_id;
		$upload_dir['url'] 		= WP_UPLOADS_URL . $blog_id . $upload_dir['subdir'];
	} else {
		$upload_dir['path']    	= WP_UPLOADS_DIR . $upload_dir['subdir'];
		$upload_dir['basedir'] 	= WP_UPLOADS_DIR;
		$upload_dir['baseurl'] 	= WP_UPLOADS_URL;
		$upload_dir['url'] 		= WP_UPLOADS_URL . $upload_dir['subdir'];
	}

	return $upload_dir;
} );