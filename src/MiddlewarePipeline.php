<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * MiddlewarePipeline is a proxy object between real PSR request handler/middleware calls
 * It is used to "glue" different pipeline parts together (Double)
 */
class MiddlewarePipeline implements RequestHandlerInterface
{
    /**
     * Current pipeline part
     *
     * @var RequestHandlerInterface|MiddlewareInterface
     */
    protected $handler;

    /**
     * Next pipeline part
     *
     * @var RequestHandlerInterface|MiddlewarePipeline
     */
    protected RequestHandlerInterface $next;

    /**
     * @param RequestHandlerInterface|MiddlewareInterface $handler points to the current pipeline call
     * @param RequestHandlerInterface|MiddlewarePipeline $next points to the next pipeline call
     */
    public function __construct($handler, RequestHandlerInterface $next)
    {
        if (!$handler instanceof RequestHandlerInterface && !$handler instanceof MiddlewareInterface) {
            throw new \InvalidArgumentException(
                'handler must be an instance of RequestHandlerInterface or MiddlewareInterface'
            );
        }

        $this->handler = $handler;
        $this->next = $next;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // call request handler
        if ($this->handler instanceof RequestHandlerInterface) {
            return $this->handler->handle($request);
        }

        // call middleware
        return $this->handler->process($request, $this->next);
    }
}
