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

class MiddlewarePipelineBuilder
{
    private MiddlewarePipelineFactory $factory;
    private RequestHandlerInterface $requestHandler;

    /**
     * @var MiddlewareInterface[]|array<int, MiddlewareInterface>
     */
    private array $middlewares = [];

    public function __construct(
        RequestHandlerInterface $requestHandler,
        ?MiddlewarePipelineFactory $pipelineFactory = null
    ) {
        $this->factory = $pipelineFactory ?? new MiddlewarePipelineFactory();
        $this->requestHandler = $requestHandler;
    }

    public function add(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @param MiddlewareInterface[]|array<int, MiddlewareInterface> $middlewares
     *
     * @return $this
     */
    public function addMany(array $middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    public function build(): MiddlewarePipeline
    {
        return $this->factory->create($this->requestHandler, $this->middlewares);
    }
}
