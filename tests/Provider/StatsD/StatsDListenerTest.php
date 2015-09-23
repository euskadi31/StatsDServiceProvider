<?php
/*
 * This file is part of the StatsDServiceProvider.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Silex\Provider\StatsD;

use Euskadi31\Silex\Provider\StatsD\StatsDListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Silex\Application;

class StatsDListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testKernelException()
    {
        $statsdMock = $this->getMockBuilder('Domnikl\Statsd\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $statsdMock->expects($this->once())
            ->method('increment')
            ->with($this->equalTo('exception.500'));

        $exceptionMock = $this->getMock('Symfony\Component\HttpKernel\Exception\HttpExceptionInterface');

        $exceptionMock->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(99));

        $getResponseForExceptionEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $getResponseForExceptionEventMock->expects($this->once())
            ->method('getException')
            ->will($this->returnValue($exceptionMock));

        $app = new Application;
        $app['statsd.options'] = [
            'events' => [
                'exception' => 'exception.<code>'
            ]
        ];

        $app['statsd'] = function() use ($statsdMock) {
            return $statsdMock;
        };

        $listener = new StatsDListener($app);

        $listener->onKernelException($getResponseForExceptionEventMock);
    }

    public function testKernelExceptionWithoutConfig()
    {
        $getResponseForExceptionEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $app = new Application;
        $app['statsd.options'] = [
            'events' => [
            ]
        ];

        $listener = new StatsDListener($app);

        $listener->onKernelException($getResponseForExceptionEventMock);
    }

    public function testKernelTerminate()
    {
        $statsdMock = $this->getMockBuilder('Domnikl\Statsd\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $statsdMock->expects($this->once())
            ->method('timing')
            ->with($this->equalTo('time'));

        $statsdMock->expects($this->once())
            ->method('gauge')
            ->with($this->equalTo('memory'));

        $statsdMock->expects($this->once())
            ->method('increment')
            ->with($this->equalTo('terminate'));

        $statsdMock->expects($this->once())
            ->method('endBatch');

        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock->server = new \Symfony\Component\HttpFoundation\ServerBag();
        $requestMock->server->set('REQUEST_TIME_FLOAT', microtime(true));

        $postResponseEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\PostResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $postResponseEventMock->method('getRequest')
            ->will($this->returnValue($requestMock));

        $app = new Application;
        $app['statsd.options'] = [
            'events' => [
                'time' => 'time',
                'memory' => 'memory',
                'terminate' => 'terminate'
            ]
        ];

        $app['statsd'] = function() use ($statsdMock) {
            return $statsdMock;
        };

        $listener = new StatsDListener($app);

        $listener->onKernelTerminate($postResponseEventMock);
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals([
            KernelEvents::EXCEPTION => [['onKernelException', Application::EARLY_EVENT]],
            KernelEvents::TERMINATE => [['onKernelTerminate', Application::EARLY_EVENT]]
        ], StatsDListener::getSubscribedEvents());
    }
}
