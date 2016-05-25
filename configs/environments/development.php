<?php

	/**
	 * FTP config
	 */
	define( 'FS_METHOD', 	'direct' );
	// define( 'FTP_USER', 	'username' );
	// define( 'FTP_PASS', 	'password' );
	// define( 'FTP_HOST', 	'host' );
	// define( 'FTP_SSL', 	false );

	/**
	 * WordPress Cache
	 */
	define( 'WP_CACHE', 		false );

	/**
	 * Debug mode
	 */
	define( 'WP_LOCAL_DEV', 	true );
	define( 'WP_DEBUG',         true );
	define( 'WP_DEBUG_LOG',     false );
	define( 'WP_DEBUG_DISPLAY', true );
	define( 'SCRIPT_DEBUG',     false );
	define( 'SAVEQUERIES',      true );

	/**
	 * Multisite Settings.
	 */
	define( 'WP_ALLOW_MULTISITE', 	false );

	/**
	 * Memory limit.
	 */
	define( 'WP_MEMORY_LIMIT', '64M' );

	/**
	 * Dissalow file/themes/plugins edits.
	 */
	define( 'AUTOMATIC_UPDATER_DISABLED', true ); // no core updates
	define( 'DISALLOW_FILE_MODS', 	false ); // no uploading plugins/themes directly
	define( 'DISALLOW_FILE_EDIT', 	false ); // no file editor
