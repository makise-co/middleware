<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Middleware\Tests;

use Closure;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function json_encode;

use const JSON_THROW_ON_ERROR;

trait MiddlewareHelper
{
    private function createRequestHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $middlewares = $request->getAttribute('middlewares');

                return new Response(200, [], json_encode($middlewares, JSON_THROW_ON_ERROR));
            }
        };
    }

    private function createMiddleware(string $name, ?Closure $closure = null): MiddlewareInterface
    {
        return new class($name, $closure) implements MiddlewareInterface {
            private ?Closure $callback;
            private string $name;

            public function __construct(string $name, ?Closure $callback = null)
            {
                $this->name = $name;
                $this->callback = $callback;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                if ($this->callback) {
                    return ($this->callback)($request, $handler);
                }

                $middlewares = $request->getAttribute('middlewares', []);
                $middlewares[] = $this->name;

                return $handler->handle(
                    $request->withAttribute('middlewares', $middlewares)
                );
            }
        };
    }
}
