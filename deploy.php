<?php
/**
 * This is an example of a deploy.php.
 */

// Load default tasks.
require( __DIR__ . '/vendor/deployer/deployer/recipe/common.php' );

// Load server's configs.
serverList( __DIR__ . '/configs/deploy.yml' );

// Repo with project.
set( 'repository', 'https://github.com/LehaMotovilov/wp.dev' );

// Lets share uploads and vendor folders.
set( 'shared_dirs', ['web/app/uploads', 'vendor'] );

// Main config with environment variables.
set( 'shared_files', ['configs/.env'] );

// Writable dirs.
set( 'writable_dirs', ['web/app/uploads', 'web/app/ewww'] );

// Flush cache.
task( 'deploy:cache', function () {
	run(
		'cd {{release_path}} &&
		vendor/wp-cli/wp-cli/bin/wp transient delete-all --allow-root &&
		vendor/wp-cli/wp-cli/bin/wp cache flush --allow-root'
	);
} )->desc('Transient deleted and Cache flushed');

// Restart services.
task( 'deploy:reload', function () {
	run(
		'sudo service nginx restart &&
		sudo service php5-fpm restart &&
		sudo /etc/init.d/memcached restart &&
		sudo service mysql restart'
	);
} )->desc('Services nginx, php5-fpm, memcached, mysql are restarted');

// Run DB migrations.
task( 'deploy:migrations', function () {
	run(
		'cd {{release_path}} &&
		vendor/bin/phinx migrate -e development'
	);
} )->desc('DB migrations');

// Run deploy.
task('deploy', [
	'deploy:prepare',
	'deploy:release',
	'deploy:update_code',
	'deploy:shared',
	'deploy:writable',
	'deploy:vendors',
	'deploy:symlink',
	'deploy:cache',
	'deploy:reload',
	'deploy:migrations',
	'cleanup'
])->desc('Deploy your project');

