<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Middleware\Tests;

use GuzzleHttp\Psr7\ServerRequest;
use MakiseCo\Middleware\Dispatcher;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase
{
    use MiddlewareHelper;

    public function testItWorks(): void
    {
        $dispatcher = new Dispatcher(
            [
                $this->createMiddleware('middleware1'),
                $this->createMiddleware('middleware2'),
                $this->createRequestHandler(),
            ]
        );

        $request = new ServerRequest('GET', '/');

        $response = $dispatcher->handle($request);

        $middlewares = \json_decode($response->getBody()->getContents());

        self::assertSame(['middleware1', 'middleware2'], $middlewares);
    }
}
