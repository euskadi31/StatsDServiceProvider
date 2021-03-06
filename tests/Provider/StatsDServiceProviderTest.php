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

use Euskadi31\Silex\Provider\StatsDServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

class StatsDServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $app = new Application;

        $app->register(new StatsDServiceProvider);

        $this->assertTrue(isset($app['statsd.options']));
        $this->assertEquals([
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
        ], $app['statsd.options']);

        $connection = $app['statsd.connection'];

        $this->assertInstanceOf('\Domnikl\Statsd\Connection\UdpSocket', $app['statsd.connection']);
        $this->assertEquals($connection, $app['statsd.connection']);

        $statsd = $app['statsd'];

        $this->assertInstanceOf('\Domnikl\Statsd\Client', $app['statsd']);
        $this->assertEquals($statsd, $app['statsd']);

        $this->assertInstanceOf('Euskadi31\Silex\Provider\StatsD\StatsDListener', $app['statsd.listener']);
    }

    public function testSubscribe()
    {
        $eventSubscriberMock = $this->getMock('Symfony\Component\EventDispatcher\EventSubscriberInterface');

        $app = new Application;
        $app['statsd.listener'] = function() use ($eventSubscriberMock) {
            return $eventSubscriberMock;
        };

        $service = new StatsDServiceProvider();

        $dispatcherMock = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcherMock->method('addSubscriber')
            ->with($this->equalTo($eventSubscriberMock));

        $service->subscribe($app, $dispatcherMock);
    }

    public function testException()
    {
        $statsdMock = $this->getMockBuilder('Domnikl\Statsd\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $statsdMock->expects($this->once())
            ->method('increment')
            ->with($this->equalTo('exception.400'));

        $app = new Application;

        $app->register(new StatsDServiceProvider);

        $app->error(function(\Exception $e, $code) {
            return new Response('We are sorry, but something went terribly wrong.');
        });

        $app['statsd'] = function() use ($statsdMock) {
            return $statsdMock;
        };

        $app->get('/', function() use ($app) {
            $app->abort(400);
        });

        $response = $app->handle(Request::create('/'));

        $this->assertEquals(400, $response->getStatusCode());
    }
}
