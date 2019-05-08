<?php
/**
 * This is an example of a deploy.php.
 */
namespace Deployer;

// Load default tasks.
require( __DIR__ . '/vendor/deployer/deployer/recipe/common.php' );

// Load server's configs.
inventory( __DIR__ . '/configs/deploy.yml' );

// Repo with project.
set( 'repository', 'https://github.com/LehaMotovilov/wp.dev' );

// Lets share uploads and vendor folders.
set( 'shared_dirs', ['web/app/uploads', 'vendor'] );

// Main config with environment variables.
set( 'shared_files', ['configs/.env'] );

// Flush cache.
task( 'deploy:cache', function () {
	run(
		'cd {{release_path}} &&
		wp-cli/wp-cli.phar transient delete-all --allow-root &&
		wp-cli/wp-cli.phar cache flush --allow-root'
	);
} )->desc('Transient deleted and Cache flushed');

// Restart services.
task( 'deploy:reload', function () {
	run(
		'sudo service nginx restart &&
		sudo service php5-fpm restart &&
		sudo service mysql restart'
	);
} )->desc('Services nginx, php5-fpm, mysql are restarted');

// Delete .git folder.
set('clear_paths', [
	'.git'
]);

// Run deploy.
task('deploy', [
	'deploy:info',
	'deploy:prepare',
	'deploy:release',
	'deploy:update_code',
	'deploy:shared',
	'deploy:vendors',
	'deploy:symlink',
	'deploy:cache',
	'deploy:reload',
	'deploy:clear_paths',
	'cleanup'
])->desc('Deploy your project');

// Print simple success.
after('deploy', 'success');
