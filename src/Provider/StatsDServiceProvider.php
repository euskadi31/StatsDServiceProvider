<?php
/*
 * This file is part of the StatsDServiceProvider.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Domnikl\Statsd;
use Euskadi31\Silex\Provider\StatsD\StatsDListener;

/**
 * StatsD integration for Silex.
 *
 * @author Axel Etcheverry <axel@etcheverry.biz>
 */
class StatsDServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $app)
    {
        $app['statsd.options'] = [
            'driver'        => '\Domnikl\Statsd\Connection\UdpSocket',
            'host'          => '127.0.0.1',
            'port'          => 8125,
            'namespace'     => '',
            'sample_rate'   => 1,
            'events'        => [
                'memory'    => 'memory',
                'time'      => 'time',
                'exception' => 'exception.<code>',
                'terminate' => 'terminate'
            ]
        ];

        $app['statsd.connection'] = function($app) {
            return new $app['statsd.options']['driver'](
                $app['statsd.options']['host'],
                $app['statsd.options']['port']
            );
        };

        $app['statsd'] = function($app) {
            $statsd = new Statsd\Client(
                $app['statsd.connection'],
                $app['statsd.options']['namespace']
            );

            $statsd->startBatch();

            return $statsd;
        };

        $app['statsd.listener'] = function($app) {
            return new StatsDListener($app);
        };
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['statsd.listener']);
    }
}
