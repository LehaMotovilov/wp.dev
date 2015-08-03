## wp.dev
Another modern WordPress stack.

## Some features:

* For deploy - [Deployer](http://deployer.org/)
* For custom DB migrations - [Phinx](https://phinx.org/)
* Dependencies and autoload - [Composer](https://getcomposer.org/)
* For environment variables - [phpdotenv](https://github.com/vlucas/phpdotenv)
* WP CLI support - [WP CLI](http://wp-cli.org/)

## How to install?

* ```mkdir test && cd test```
* ```git clone git@github.com:LehaMotovilov/wp.dev.git .```
* ```composer install```
* ```mv configs/.env.example configs/.env && nano configs/.env```
* ```mv configs/deploy.yml.example configs/deploy.yml && nano configs/deploy.yml```

Enjoy :)