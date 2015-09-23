# Silex StatsD Service Provider

[![Build Status](https://img.shields.io/travis/euskadi31/StatsDServiceProvider/master.svg)](https://travis-ci.org/euskadi31/StatsDServiceProvider)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/235980ff-5681-471f-b455-e18dcefc88b0.svg)](https://insight.sensiolabs.com/projects/235980ff-5681-471f-b455-e18dcefc88b0)
[![Coveralls](https://img.shields.io/coveralls/euskadi31/StatsDServiceProvider.svg)](https://coveralls.io/github/euskadi31/StatsDServiceProvider)
[![HHVM](https://img.shields.io/hhvm/euskadi31/StatsDServiceProvider.svg)](https://travis-ci.org/euskadi31/StatsDServiceProvider)
[![Packagist](https://img.shields.io/packagist/v/euskadi31/statsd-service-provider.svg)](https://packagist.org/packages/euskadi31/statsd-service-provider)


## Install

Add `euskadi31/statsd-service-provider` to your `composer.json`:

    % php composer.phar require euskadi31/statsd-service-provider:~2.0

## Usage

### Configuration

```php
<?php

$app = new Silex\Application;

$app->register(new \Euskadi31\Silex\Provider\StatsDServiceProvider);
```

## License

StatsDServiceProvider is licensed under [the MIT license](LICENSE.md).
