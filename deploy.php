<?php

	// All Deployer recipes are based on `recipe/common.php`.
	require( __DIR__ . '/vendor/deployer/recipe/wordpress.php' );

	serverList( __DIR__ . '/configs/deploy.yml' );

	set( 'repository', 'https://github.com/LehaMotovilov/wp.dev' );

	task('reload:nginx', function () {
	    run('sudo service nginx restart');
	});

	task('reload:php', function () {
	    run('sudo service php5-fpm restart');
	});

	after('deploy', 'reload:nginx');
	// after('rollback', 'reload:php-fpm');