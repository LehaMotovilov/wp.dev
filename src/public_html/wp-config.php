<?php
/**
 * Wp-config.php loaded by WP core.
 *
 * @package WordPress
 */

// Composer's autoloader.
require_once( dirname( __DIR__ ) . '/vendor/autoload.php' );

// Application config.
require_once( dirname( __DIR__ ) . '/configs/main.php' );

// WP settings.
require_once( ABSPATH . 'wp-settings.php' );
