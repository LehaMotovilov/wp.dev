<?php
	/**
	 * Dynamically load environment variables from file .env
	 */
	if ( file_exists( __DIR__ . '/.env' ) ) {
	  	$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
		$dotenv->load();
		$dotenv->required([
			'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'WP_HOME', 'WP_SITEURL', 'DB_PREFIX'
		]);
	}

	/**
	 * Set up our global environment constant and load its config first
	 * Default: development
	 */
	define( 'WP_ENV', getenv( 'WP_ENV' ) ?: 'development' );
	$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';
	if ( file_exists( $env_config ) ) {
		require_once( $env_config );
	}

	/**
	 * URLs.
	 */
	define( 'WP_HOME', 			getenv( 'WP_HOME' ) );
	define( 'WP_SITEURL', 		getenv( 'WP_SITEURL' ) . '/wp' );

	/**
	 * Custom Content Directory.
	 */
	define( 'CONTENT_DIR', 		'/app' );
	define( 'WEB_ROOT_PATH', 	dirname(__DIR__) . '/public_html');
	define( 'WP_CONTENT_DIR', 	dirname(__DIR__) . '/public_html' . CONTENT_DIR );
	define( 'WP_CONTENT_URL', 	WP_HOME . CONTENT_DIR );

	/**
	 * DB settings.
	 */
	define( 'DB_NAME', 			getenv( 'DB_NAME' ) );
	define( 'DB_USER', 			getenv( 'DB_USER' ) );
	define( 'DB_PASSWORD', 		getenv( 'DB_PASSWORD' ) );
	define( 'DB_HOST', 			getenv( 'DB_HOST' ) ?: 'localhost' );
	define( 'DB_CHARSET', 		'utf8' );
	define( 'DB_COLLATE', 		'' );
	$table_prefix = 			getenv( 'DB_PREFIX' );

	/**
	 * Authentication Unique Keys and Salts.
	 * See here: https://api.wordpress.org/secret-key/1.1/salt
	 */
	define( 'AUTH_KEY', 		getenv( 'WP_AUTH_KEY' ) ?: "********" );
	define( 'SECURE_AUTH_KEY', 	getenv( 'WP_SECURE_AUTH_KEY' ) ?: "********" );
	define( 'LOGGED_IN_KEY', 	getenv( 'WP_LOGGED_IN_KEY' ) ?: "********" );
	define( 'NONCE_KEY', 		getenv( 'WP_NONCE_KEY' ) ?: "********" );
	define( 'AUTH_SALT', 		getenv( 'WP_AUTH_SALT' ) ?: "********" );
	define( 'SECURE_AUTH_SALT', getenv( 'WP_SECURE_AUTH_SALT' ) ?: "********" );
	define( 'LOGGED_IN_SALT', 	getenv( 'WP_LOGGED_IN_SALT' ) ?: "********" );
	define( 'NONCE_SALT', 		getenv( 'WP_NONCE_SALT' ) ?: "********" );

	/**
	 * AutoSave Interval.
	 */
	define( 'AUTOSAVE_INTERVAL', 3600 ); // Auto-save 1x per hour.

	/**
	 * Disable Post Revisions.
	 */
	define( 'WP_POST_REVISIONS', false ); // No revisions.

	/**
	 * Media Trash.
	 */
	define( 'MEDIA_TRASH', 		true ); // Don't use trash for media files.

	/**
	 * Trash Days.
	 */
	define( 'EMPTY_TRASH_DAYS',	0 ); // Don't use trash.

	/**
	 * Default theme.
	 */
	define( 'WP_DEFAULT_THEME', 'twentytwentytwo' );

	/**
	 * Bootstrap WordPress.
	 */
	if ( !defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', dirname(__DIR__) . '/public_html/wp/');
	}
