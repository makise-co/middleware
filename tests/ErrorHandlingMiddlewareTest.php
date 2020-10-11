<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Middleware\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use MakiseCo\Middleware\ErrorHandlerInterface;
use MakiseCo\Middleware\ErrorHandlingMiddleware;
use MakiseCo\Middleware\MiddlewarePipeFactory;
use PHPUnit\Framework\TestCase;

class ErrorHandlingMiddlewareTest extends TestCase
{
    use MiddlewareHelper;

    public function testErrorCaughtInMiddleware(): void
    {
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);

        $exception = new \InvalidArgumentException('Something went wrong');

        $pipeline = (new MiddlewarePipeFactory())
            ->create(
                [
                    new ErrorHandlingMiddleware($errorHandler),
                    $this->createMiddleware(
                        'fail',
                        static function () use ($exception) {
                            throw $exception;
                        }
                    ),
                    $this->createRequestHandler(),
                ]
            );

        $request = new ServerRequest('GET', '/');

        $errorResponse = new Response(500, [], $exception->getMessage());

        $errorHandler
            ->expects(self::once())
            ->method('handle')
            ->with($exception, $request)
            ->willReturn($errorResponse);

        $response = $pipeline->handle($request);

        self::assertSame($errorResponse->getStatusCode(), $response->getStatusCode());
        self::assertSame($exception->getMessage(), $response->getBody()->getContents());
    }

    public function testErrorCaughtInRequestHandler(): void
    {
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);

        $exception = new \InvalidArgumentException('Something went wrong');

        $pipeline = (new MiddlewarePipeFactory())
            ->create(
                [
                    new ErrorHandlingMiddleware($errorHandler),
                    $this->createMiddleware(
                        'fail',
                        static function () use ($exception) {
                            throw $exception;
                        }
                    ),
                    $this->createRequestHandler(),
                ]
            );

        $request = new ServerRequest('GET', '/');

        $errorResponse = new Response(500, [], $exception->getMessage());

        $errorHandler
            ->expects(self::once())
            ->method('handle')
            ->with($exception, $request)
            ->willReturn($errorResponse);

        $response = $pipeline->handle($request);

        self::assertSame($errorResponse->getStatusCode(), $response->getStatusCode());
        self::assertSame($exception->getMessage(), $response->getBody()->getContents());
    }
}
