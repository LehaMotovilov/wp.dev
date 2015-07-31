<?php

	// Load default tasks
	require( __DIR__ . '/vendor/deployer/deployer/recipe/common.php' );

	// Load server's configs
	serverList( __DIR__ . '/configs/deploy.yml' );

	// Repo with project
	set( 'repository', 'https://github.com/LehaMotovilov/wp.dev' );

	// Lets share uploads and vendor folders
	set( 'shared_dirs', ['uploads', 'vendor'] );

	// Main config with environment variables
	set( 'shared_files', ['configs/.env'] );

	// Writable dirs
	set( 'writable_dirs', ['uploads'] );

	// Default tasks
	task('deploy', [
		'deploy:prepare',
		'deploy:release',
		'deploy:update_code',
		'deploy:shared',
		'deploy:writable',
		'deploy:vendors',
		'deploy:symlink',
		'cleanup',
	])->desc('Deploy your project');

	// Custom task
	task( 'reload', function () {
		run('sudo service nginx restart');
		run('sudo service php5-fpm restart');
	} )->desc('Nginx and php5-fpm restart');

	// Run DB migrations
	task( 'migrations', function () {
		run('vendor/bin/phinx migrate -e development');
	} )->desc('DB migrations');

	after( 'deploy', 'reload' );
	after( 'deploy', 'migrations' );
	after( 'deploy', 'success' );
