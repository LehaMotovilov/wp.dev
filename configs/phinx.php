<?php

	/**
	 * Dynamically load environment variables from file .env
	 */
	if ( file_exists( __DIR__ . '/.env' ) ) {
		$dotenv = new Dotenv\Dotenv( __DIR__ );
		$dotenv->load();
		$dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'WP_HOME', 'WP_SITEURL']);
	}

	// Return
	return [
		"paths" => [
			"migrations" => "migrations"
		],
		"environments" => [
			"default_migration_table" => getenv( 'DB_PREFIX' ) . "phinxlog",
			"default_database" => getenv( 'DB_NAME' ),
			"development" => [
				"adapter" => "mysql",
				"host" => getenv( 'DB_HOST' ),
				"name" => getenv( 'DB_NAME' ),
				"user" => getenv( 'DB_USER' ),
				"pass" => getenv( 'DB_PASSWORD' )
			]
		]
	];