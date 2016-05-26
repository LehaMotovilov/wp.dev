<?php

	/**
	 * FTP config
	 */
	 define( 'FS_METHOD', 'direct' );
	// define( 'FTP_USER', 	'username' );
	// define( 'FTP_PASS', 	'password' );
	// define( 'FTP_HOST', 	'host' );
	// define( 'FTP_SSL', 	false );

	/**
	 * WordPress Cache
	 */
	define( 'WP_CACHE', 		true );

	/**
	 * Debug mode
	 */
	define( 'WP_LOCAL_DEV', 	false );
	define( 'WP_DEBUG',         false );
	define( 'WP_DEBUG_LOG',     false );
	define( 'WP_DEBUG_DISPLAY', false );
	define( 'SCRIPT_DEBUG',     false );
	define( 'SAVEQUERIES',      false );

	/**
	 * Multisite Settings.
	 */
	define( 'WP_ALLOW_MULTISITE', 	false );

	/**
	 * Memory limit.
	 */
	define( 'WP_MEMORY_LIMIT', '96M' );

	/**
	 * SSL support.
	 */
	define( 'FORCE_SSL_LOGIN', false );
	define( 'FORCE_SSL_ADMIN', false );

	/**
	 * Dissalow file/themes/plugins edits.
	 */
	define( 'AUTOMATIC_UPDATER_DISABLED', true ); // no core updates
	define( 'DISALLOW_FILE_MODS', 	true ); // no uploading plugins/themes directly
	define( 'DISALLOW_FILE_EDIT', 	true ); // no file editor
