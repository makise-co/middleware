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
use MakiseCo\Middleware\MiddlewarePipeFactory;
use PHPUnit\Framework\TestCase;

class MiddlewarePipelineTest extends TestCase
{
    use MiddlewareHelper;

    public function testEmptyPipeline(): void
    {
        $factory = new MiddlewarePipeFactory();
        $pipeline = $factory->create([$this->createRequestHandler()]);

        $request = new ServerRequest('GET', '/');
        $response = $pipeline->handle($request);

        $middlewares = \json_decode($response->getBody()->getContents());

        self::assertEmpty($middlewares);
    }

    public function testMiddlewarePipeline(): void
    {
        $middleware1 = $this->createMiddleware('middleware1');
        $middleware2 = $this->createMiddleware('middleware2');

        $factory = new MiddlewarePipeFactory();
        $pipeline = $factory->create([$middleware1, $middleware2, $this->createRequestHandler()]);

        $request = new ServerRequest('GET', '/');
        $response = $pipeline->handle($request);

        $middlewares = \json_decode($response->getBody()->getContents());

        self::assertSame(['middleware1', 'middleware2'], $middlewares);
    }

    public function testMergePipelines(): void
    {
        $middleware1 = $this->createMiddleware('middleware1');
        $middleware2 = $this->createMiddleware('middleware2');

        $factory = new MiddlewarePipeFactory();
        $subPipeline = $factory->create(
            [
                $this->createMiddleware('middleware1.1'),
                $this->createMiddleware('middleware1.2'),
            ]
        );

        $pipeline = $factory->create(
            [
                $middleware1,
                $subPipeline,
                $middleware2,
                $this->createRequestHandler(),
            ]
        );

        $request = new ServerRequest('GET', '/');
        $response = $pipeline->handle($request);

        $middlewares = \json_decode($response->getBody()->getContents());

        self::assertSame(['middleware1', 'middleware1.1', 'middleware1.2', 'middleware2'], $middlewares);
    }

    public function testMergePipelinesWithRequestHandler(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = new MiddlewarePipeFactory();
        $subPipeline = $factory->create(
            [
                $this->createMiddleware('middleware1.1'),
                $this->createRequestHandler()
            ]
        );

        $pipeline = $factory->create(
            [
                $this->createMiddleware('middleware1'),
                $subPipeline,
                $this->createRequestHandler(),
            ]
        );
    }
}
