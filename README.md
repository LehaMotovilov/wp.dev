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
* Login admin/admin - http://wp.test/wp/wp-admin/
* Optional ```mv configs/deploy.yml.example configs/deploy.yml && nano configs/deploy.yml```

## How to use?

* Deploy example ```vendor/bin/dep deploy production```
* Run Migrations ```composer migrate:dev```
* Run Tests ```composer tests```
* Run PHP Code Style check ```composer cs```
* Run PHP Copy Paste Detector check ```composer cp```
* Run PHP Mess Detector check ```composer md```
* Run WP-CLI example ```wp-cli/wp-cli.phar --info```
