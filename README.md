## wp.dev
Another modern WordPress stack. Enjoy 🤓 👍

## Features:

* Docker support - [Docker](https://www.docker.com/)
* Dependency Manager for PHP - [Composer](https://getcomposer.org/)
* Dependency Manager for WP (plugins/themes) - [WordPress Packagist](https://wpackagist.org/)
* For environment variables - [phpdotenv](https://github.com/vlucas/phpdotenv)
* WP CLI support - [WP CLI](http://wp-cli.org/)
* PHP Code Style for WordPress - [Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)
* PHP Mess Detector - [Mess Detector](https://phpmd.org/)

## How to install?

### Install with Docker
* Install [Docker](https://www.docker.com/)
* Run ```docker-compose up -d```
* Run ```docker-compose exec php composer install```
* Run ```docker-compose exec php composer wp:import```
* Open website - [localhost](http://localhost/)
* Admin area(credentials: admin/admin) - [localhost](http://localhost/wp/wp-admin)

### How to use?

* Run PHP Code Style check ```docker-compose exec php composer cs```
* Run PHP Mess Detector check ```docker-compose exec php composer md```
* Run WP-CLI example ```docker-compose exec php composer wp:info```

### How to backup DB and images locally?

* Export DB and Uploads ```docker-compose exec php composer wp:export```
* Import DB and Uploads ```docker-compose exec php composer wp:import```
