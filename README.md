## wp.dev
Another modern WordPress stack. Enjoy :)

## Some features:

* For deploy - [Deployer](http://deployer.org/)
* For custom DB migrations - [Phinx](https://phinx.org/)
* Dependency Manager for PHP - [Composer](https://getcomposer.org/)
* JS Manager - [Bower](http://bower.io/)
* JS Task Manager - [Grunt](http://gruntjs.com/)
* For environment variables - [phpdotenv](https://github.com/vlucas/phpdotenv)
* WP CLI support - [WP CLI](http://wp-cli.org/)
* Codeception for testing - [Codeception](http://codeception.com/)
* PHP Code Style for WordPress - [Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)

## How to install?

* ```mkdir test && cd test```
* ```git clone git@github.com:LehaMotovilov/wp.dev.git .```
* ```composer install```
* ```mv configs/.env.example configs/.env && nano configs/.env```
* ```mv configs/deploy.yml.example configs/deploy.yml && nano configs/deploy.yml```
* ```npm install```
* ```bower install```

## How to use?

* Migration example ```vendor/bin/phinx migrate -e development```
* Deploy example ```dep deploy production```
* Run tests ```php ./vendor/bin/codecept run```
* Run Code Style check ```vendor/bin/phpcs --standard=phpcs.ruleset.xml web/ -s```
* Run WP-Cli example ```vendor/wp-cli/wp-cli/bin/wp core version```

## Apache config
```
<VirtualHost *:80>
	ServerAdmin lehaqs@gmail.com
	DocumentRoot "/Users/alekseymotovilov/Sites/wp.dev/web"
	ServerName wp.dev
	ServerAlias www.wp.dev
	ErrorLog "/private/var/log/apache2/wp-error_log"
	CustomLog "/private/var/log/apache2/wp-access_log" common

	<Directory "/Users/alekseymotovilov/Sites/wp.dev/web">
		Options Indexes FollowSymLinks
		AllowOverride All
		Require all granted
	</Directory>
</VirtualHost>
```

## Nginx config
```
server {
	listen 80 default_server;
	listen [::]:80 default_server ipv6only=on;

	root /var/www/nginx/html/current/web;
	index index.php index.html;

	server_name wp.dev;

	location / {
		try_files $uri $uri/ =404;
	}

	location ~* /(?:uploads|files)/.*\.php$ {
		deny all;
	}

	if (!-e $request_filename) {
		rewrite ^(.+)$ /index.php?q=$1 last;
	}

	location ~ \.php$ {
		try_files $uri =404;
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		include fastcgi_params;
	}
}
```
