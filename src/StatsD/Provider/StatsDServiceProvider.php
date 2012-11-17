<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StatsD\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use StatsD;

/**
 * StatsD Provider.
 *
 * @author Axel Etcheverry <axel@etcheverry.biz>
 */
class StatsDServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['statsd.enabled']  = true;
        $app['statsd.host']     = '127.0.0.1';
        $app['statsd.port']     = '8125';

        $app['statsd'] = $app->share(function ($app) {
            return new StatsD\Client(array(
                'enabled'   => $app['statsd.enabled'],
                'host'      => $app['statsd.host'],
                'port'      => $app['statsd.port']
            ));
        });
    }

    public function boot(Application $app)
    {
    }
}