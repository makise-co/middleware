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

interface MiddlewareResolverInterface
{
    /**
     * @param string|MiddlewareInterface|RequestHandlerInterface $middleware
     *
     * @return MiddlewareInterface|RequestHandlerInterface
     */
    public function resolve($middleware);
}
