<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;

use function get_class;
use function gettype;

class MiddlewareResolver implements MiddlewareResolverInterface
{
    private ?ContainerInterface $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param MiddlewareInterface|RequestHandlerInterface|string $middleware
     *
     * @return MiddlewareInterface|RequestHandlerInterface
     */
    public function resolve($middleware)
    {
        if (is_string($middleware) && $this->container !== null) {
            $middleware = $this->container->get($middleware);
        }

        if (!$middleware instanceof MiddlewareInterface && !$middleware instanceof RequestHandlerInterface) {
            $type = gettype($middleware);
            if ($type === 'object') {
                $type = get_class($middleware);
            }

            throw new TypeError(
                "Middleware must implement MiddlewareInterface or RequestHandlerInterface " .
                "instance of {$type} got"
            );
        }

        return $middleware;
    }
}
