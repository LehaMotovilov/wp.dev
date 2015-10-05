## wp.dev
Another modern WordPress stack. Enjoy :)

## Some features:

* For deploy - [Deployer](http://deployer.org/)
* For custom DB migrations - [Phinx](https://phinx.org/)
* PHP Dependencies and autoload - [Composer](https://getcomposer.org/)
* JS Manager - [Bower](http://bower.io/)
* JS Task Manager - [Grunt](http://gruntjs.com/)
* For environment variables - [phpdotenv](https://github.com/vlucas/phpdotenv)
* WP CLI support - [WP CLI](http://wp-cli.org/)
* Codeception for testing - [Codeception](http://codeception.com/)

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
* Run tests ```php ./vendor/bin/codecept run ```