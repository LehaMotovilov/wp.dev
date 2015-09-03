<?php
/**
 * 	Plugin Name: Custom robots.txt
 *	Description: Add custom rules to robots.txt
 *	Version:     1.0.0
 *	Plugin URI:
 *	Author:      LehaMotovilov
 *	Author URI:  http://lehamotovilov.com/
 *	Network: 	 True
 *	License:     GPL v3
 */

/**
 * Rewrite robots.txt
 */
add_filter( 'robots_txt', function( $output, $public ) {
	$content = get_option( 'robots_txt_content_rewrited' );
	if ( !empty( $content ) ) {
		return $content;
	} else {
		return $output;
	}
}, 10, 2 );
