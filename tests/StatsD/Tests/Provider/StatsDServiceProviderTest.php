<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StatsD\Tests\Provider;

use Silex\Application;
use StatsD\Provider\StatsDServiceProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * StatsDServiceProvider test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class StatsDServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $app = new Application();

        $app->register(new StatsDServiceProvider());

        $app->get('/', function () {});

        $request = Request::create('/');
        $app->handle($request);

        $this->assertInstanceOf('StatsD\Client', $app['statsd']);
    }
}