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
use MakiseCo\Middleware\MiddlewarePipelineBuilder;
use PHPUnit\Framework\TestCase;

class MiddlewarePipelineBuilderTest extends TestCase
{
    use MiddlewareHelper;

    public function testBuilder(): void
    {
        $builder = new MiddlewarePipelineBuilder($this->createRequestHandler());
        $builder->add($this->createMiddleware('middleware1'));
        $builder->addMany(
            [
                $this->createMiddleware('middleware2'),
                $this->createMiddleware('middleware3'),
            ]
        );

        $pipeline = $builder->build();

        $request = new ServerRequest('GET', '/');
        $response = $pipeline->handle($request);

        $middlewares = \json_decode($response->getBody()->getContents());

        self::assertSame(['middleware1', 'middleware2', 'middleware3'], $middlewares);
    }
}
