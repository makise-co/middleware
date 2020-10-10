<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_reverse;

class MiddlewarePipelineFactory
{
    /**
     * @param RequestHandlerInterface $handler Application request handler
     * @param MiddlewareInterface[]|array<int, MiddlewareInterface> $middlewares
     *
     * @return MiddlewarePipeline
     */
    public function create(RequestHandlerInterface $handler, array $middlewares): MiddlewarePipeline
    {
        if ([] === $middlewares) {
            return new MiddlewarePipeline($handler, new EmptyRequestHandler());
        }

        $pipe = $handler;

        foreach (array_reverse($middlewares) as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new \InvalidArgumentException("Middleware must implement MiddlewareInterface");
            }

            $pipe = new MiddlewarePipeline($middleware, $pipe);
        }

        /** @var MiddlewarePipeline */
        return $pipe;
    }
}
