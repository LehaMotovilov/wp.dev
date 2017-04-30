## wp.dev
Another modern WordPress stack. Enjoy :)
I highly recommend to use it with [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV)

## Some features:

* For deploy - [Deployer](http://deployer.org/)
* For custom DB migrations - [Phinx](https://phinx.org/)
* Dependency Manager for PHP - [Composer](https://getcomposer.org/)
* Dependency Manager for WP (plugins/themes) - [WordPress Packagist](https://wpackagist.org/)
* For environment variables - [phpdotenv](https://github.com/vlucas/phpdotenv)
* WP CLI support - [WP CLI](http://wp-cli.org/)
* Codeception for testing - [Codeception](http://codeception.com/)
* PHP Code Style for WordPress - [Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)
* PHP Copy Paste Detector - [Copy/Paste Detector](https://github.com/sebastianbergmann/phpcpd)
* PHP Mess Detector - [Mess Detector](https://phpmd.org/)
* JS Code Style for WordPress - [Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/javascript/)

## How to install?

* ```composer install```
* ```mv configs/.env.example configs/.env && nano configs/.env```
* Import example DB ```wp-cli/wp-cli.phar db import configs/db/dump.sql```
* Login admin/admin - http://wp.dev/wp/wp-admin/
* Optional ```mv configs/deploy.yml.example configs/deploy.yml && nano configs/deploy.yml```

## How to use?

* Deploy example ```vendor/bin/dep deploy production```
* Run Migrations ```composer migrate:dev```
* Run Tests ```composer tests```
* Run PHP Code Style check ```composer cs```
* Run PHP Copy Paste Detector check ```composer cp```
* Run PHP Mess Detector check ```composer md```
* Run JS Code Style check ```jscs web/app/themes/twentyfifteen```
* Run WP-CLI example ```wp-cli/wp-cli.phar core version```

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
