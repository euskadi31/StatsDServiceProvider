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

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Silex\Application;

/**
 * StatsD Listener.
 *
 * @author Axel Etcheverry <axel@etcheverry.biz>
 */
class StatsDListener implements EventSubscriberInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     *
     * @param  GetResponseForExceptionEvent $event
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (
            !isset($this->app['statsd.options']['events']['exception']) ||
            empty($this->app['statsd.options']['events']['exception'])
        ) {
            return;
        }

        $exception = $event->getException();

        $code = ($exception instanceof HttpExceptionInterface) ? $exception->getStatusCode() : $exception->getCode();
        if ($code < 100 || $code >= 600) {
            $code = 500;
        }

        $this->app['statsd']->increment(str_replace(
            '<code>',
            $code,
            $this->app['statsd.options']['events']['exception']
        ));
    }

    /**
     * method called on the kernel.terminate event
     *
     * @param PostResponseEvent $event event
     *
     * @return void
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (
            isset($this->app['statsd.options']['events']['time']) &&
            !empty($this->app['statsd.options']['events']['time'])
        ) {
            $request   = $event->getRequest();
            $startTime = $request->server->get('REQUEST_TIME_FLOAT', $request->server->get('REQUEST_TIME'));
            $time      = (microtime(true) - $startTime);
            $time      = round($time * 1000);

            $this->app['statsd']->timing($this->app['statsd.options']['events']['time'], $time);
        }

        if (
            isset($this->app['statsd.options']['events']['memory']) &&
            !empty($this->app['statsd.options']['events']['memory'])
        ) {
            $memory = memory_get_peak_usage(true);
            $memory = ($memory > 1024 ? intval($memory / 1024) : 0);

            $this->app['statsd']->gauge($this->app['statsd.options']['events']['memory'], $memory);
        }

        if (
            isset($this->app['statsd.options']['events']['terminate']) &&
            !empty($this->app['statsd.options']['events']['terminate'])
        ) {
            $this->app['statsd']->increment($this->app['statsd.options']['events']['terminate']);
        }

        $this->app['statsd']->endBatch();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [['onKernelException', -4]],
            KernelEvents::TERMINATE => [['onKernelTerminate', Application::EARLY_EVENT]]
        ];
    }
}
