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
use RuntimeException;

/**
 * Flat list middleware dispatcher implementation
 */
class Dispatcher implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]|RequestHandlerInterface[]
     */
    private array $steps = [];

    private int $currentStep = 0;

    /**
     * @param MiddlewareInterface[]|RequestHandlerInterface[]|string[] $middlewares
     * @param MiddlewareResolverInterface|null $resolver
     */
    public function __construct(array $middlewares, ?MiddlewareResolverInterface $resolver = null)
    {
        $resolver ??= new MiddlewareResolver();

        foreach ($middlewares as $middleware) {
            $this->steps[] = $resolver->resolve($middleware);
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $step = $this->steps[$this->currentStep] ?? null;
        if (null === $step) {
            throw new RuntimeException("Step {$this->currentStep} not found");
        }

        $this->currentStep++;

        if ($step instanceof MiddlewareInterface) {
            return $step->process($request, $this);
        }

        if ($step instanceof RequestHandlerInterface) {
            $this->currentStep = 0;

            return $step->handle($request);
        }
    }
}
