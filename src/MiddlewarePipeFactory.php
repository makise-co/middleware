<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Middleware;

use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_reverse;
use function array_shift;

class MiddlewarePipeFactory implements MiddlewarePipeFactoryInterface
{
    private MiddlewareResolverInterface $resolver;

    public function __construct(?MiddlewareResolverInterface $resolver = null)
    {
        $this->resolver = $resolver ?? new MiddlewareResolver();
    }

    /**
     * @param string[]|MiddlewareInterface[]|RequestHandlerInterface[] $middlewares
     *
     * @return MiddlewarePipe
     *
     * @throws InvalidArgumentException
     */
    public function create(array $middlewares): MiddlewarePipe
    {
        if ([] === $middlewares) {
            throw new InvalidArgumentException('Pipeline cannot be empty');
        }

        $middlewares = array_reverse($middlewares);

        /** @var MiddlewareInterface|RequestHandlerInterface|string $firstMiddleware */
        $firstMiddleware = array_shift($middlewares);

        $pipe = new MiddlewarePipe(
            $this->resolver->resolve($firstMiddleware),
            new EmptyRequestHandler()
        );

        if ([] === $middlewares) {
            return $pipe;
        }

        foreach ($middlewares as $middleware) {
            $middleware = $this->resolver->resolve($middleware);

            if ($middleware instanceof MiddlewarePipe) {
                // safe copying the pipeline to prevent side effects of merging
                $pipe = $this->mergePipelines(clone $middleware, $pipe);
            } else {
                $pipe = new MiddlewarePipe($middleware, $pipe);
            }
        }

        return $pipe;
    }

    protected function mergePipelines(MiddlewarePipe $pipe, MiddlewarePipe $mergeInto): MiddlewarePipe
    {
        $endOfPipe = $pipe;

        while (($nextPipe = $endOfPipe->getNext()) instanceof MiddlewarePipe) {
            $handler = $nextPipe->getHandler();
            if (!$handler instanceof EmptyRequestHandler && $handler instanceof RequestHandlerInterface) {
                throw new InvalidArgumentException('You can\'t merge pipeline with request handler');
            }

            $endOfPipe = $nextPipe;
        }

        $endOfPipe->setNext($mergeInto);

        return $pipe;
    }
}
