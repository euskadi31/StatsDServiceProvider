StatsD Silex Provider
=====================

[![Build Status](https://secure.travis-ci.org/euskadi31/StatsDServiceProvider.png?branch=master)](https://travis-ci.org/euskadi31/StatsDServiceProvider)

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new StatsD\Provider\StatsDServiceProvider(), array(
    'statsd.host' => '127.0.0.1',
    'statsd.port' => '8125'
));

$app->get('/hello/{name}', function ($name) use ($app) {
    
    $app['statsd']->increment('hello');

    return 'Hello ' . $app->escape($name);
});

$app->run();
```

## Installation

The recommended way to install StatsDServiceProvider is [through
composer](http://getcomposer.org). Just create a `composer.json` file and
run the `php composer.phar install` command to install it:

    {
        "minimum-stability": "dev",
        "require": {
            "silex/silex": "1.0.*",
            "euskadi31/statsd-service-provider": "dev-master"
        }
    }

## Tests

To run the test suite, you need [composer](http://getcomposer.org) and
[PHPUnit](https://github.com/sebastianbergmann/phpunit).

    $ php composer.phar install --dev
    $ phpunit

## License

StatsDServiceProvider is licensed under the MIT license.