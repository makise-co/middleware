<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

class Handler implements RequestHandlerInterface
{
    private Response $response;

    public function __construct()
    {
        $this->response = new Response(200, [], 'It works');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response;
    }
}

class Middleware implements MiddlewareInterface
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middlewares = $request->getAttribute('middlewares', []);
        $middlewares[] = $this->name;

        return $handler->handle(
            $request->withAttribute('middlewares', $middlewares)
        );
    }
}

$iterCount = 1_000_000;
$request = new \GuzzleHttp\Psr7\ServerRequest('GET', '/');
$handler = new Handler();
$middleware1 = new Middleware('middleware1');
$middleware2 = new Middleware('middleware2');
$middleware3 = new Middleware('middleware3');

function testMakise(): float
{
    global $handler, $middleware1, $middleware2, $middleware3, $iterCount, $request;

    $factory = new \MakiseCo\Middleware\MiddlewarePipeFactory();
    $pipeline = $factory->create(
        [
            $middleware1,
            $middleware2,
            $middleware3,
            $handler,
        ]
    );

    $start = microtime(true);

    for ($i = 0; $i < $iterCount; $i++) {
        $response = $pipeline->handle($request);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Bad response');
        }
    }

    $end = microtime(true);

    return $end - $start;
}

function testMakiseFlat(): float
{
    global $handler, $middleware1, $middleware2, $middleware3, $iterCount, $request;

    $dispatcher = new \MakiseCo\Middleware\Dispatcher(
        [
            $middleware1,
            $middleware2,
            $middleware3,
            $handler,
        ]
    );

    $start = microtime(true);

    for ($i = 0; $i < $iterCount; $i++) {
        $response = $dispatcher->handle($request);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Bad response');
        }
    }

    $end = microtime(true);

    return $end - $start;
}

function testLaminas(): float
{
    global $handler, $middleware1, $middleware2, $middleware3, $iterCount, $request;

    $app = new \Laminas\Stratigility\MiddlewarePipe();

    $app->pipe($middleware1);
    $app->pipe($middleware2);
    $app->pipe($middleware3);
    $app->pipe(new \Laminas\Stratigility\Middleware\RequestHandlerMiddleware($handler));

    $start = microtime(true);

    for ($i = 0; $i < $iterCount; $i++) {
        $response = $app->handle($request);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Bad response');
        }
    }

    $end = microtime(true);

    return $end - $start;
}

function testRelay(): float
{
    global $handler, $middleware1, $middleware2, $middleware3, $iterCount, $request;

    $queue[] = $middleware1;
    $queue[] = $middleware2;
    $queue[] = $middleware3;
    $queue[] = $handler;

    $relay = new \Relay\Relay($queue);

    $start = microtime(true);

    for ($i = 0; $i < $iterCount; $i++) {
        $response = $relay->handle($request);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Bad response');
        }
    }

    $end = microtime(true);

    return $end - $start;
}

printf("Makise time took: %f secs (%.8f secs per request)\n", ($res = testMakise()), $res / $iterCount);
printf("Makise (flat) time took: %f secs (%.8f secs per request)\n", ($res = testMakiseFlat()), $res / $iterCount);
printf("Laminas time took: %f secs (%.8f secs per request)\n", ($res = testLaminas()), $res / $iterCount);
printf("Relay time took: %f secs (%.8f secs per request)\n", ($res = testRelay()), $res / $iterCount);
