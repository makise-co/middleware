# PSR-15 Request dispatcher

This package provides a two high performance implementations of the PSR-15 request dispatcher
* Dispatcher - Flat List implementation

    This is a centralized architecture, the Dispatcher acts as the coordinator. 
    Middleware receives the Dispatcher instance as the request handler. 
    And the Dispatcher knows which next Middleware needs to be called.
    It works like: 
    * Dispatcher->handle($request) ->
    * Middleware1->process($request, Dispatcher) ->
    * Dispatcher->handle($request) ->
    * Middleware2->process($request, Dispatcher) ->
    * Dispatcher->handle($request) ->
    * RequestHandler->handle($request)

* MiddlewarePipe - Linked List implementation (harder to understand, but works a bit faster)

    This is a decentralized architecture, each pipeline acts as a request handler.
    MiddlewarePipe is a wrapper over a middleware or a request handler.
    It works like: 
    * $pipeline->handle($request) ->
    * Middleware1->process($request, $nextPipeline) ->
    * $nextPipeline->handle($request) ->
    * Middleware2->process($request, $nextPipeline) ->
    * $nextPipeline->handle($request) ->
    * RequestHandler->handle($request)

## Requirements
* PHP >= 7.4

## Benchmarks
10000 calls:
```
Makise time took: 0.011235 secs (0.00000112 secs per request)
Makise (flat) time took: 0.011854 secs (0.00000119 secs per request)
Laminas time took: 0.034769 secs (0.00000348 secs per request)
Relay time took: 0.021777 secs (0.00000218 secs per request)
```

1 million calls:
```
Makise time took: 1.169399 secs (0.00000117 secs per request)
Makise (flat) time took: 1.231906 secs (0.00000123 secs per request)
Laminas time took: 2.112726 secs (0.00000211 secs per request)
Relay time took: 1.490263 secs (0.00000149 secs per request)
```

* Laminas version used: 3.2.2
* Relay version used: 2.1.1

Benchmark code can be found [here](benchmark/bench.php).

## Usage

### Dispatcher (Flat List)
```php
<?php

declare(strict_types=1);

use MakiseCo\Middleware\Dispatcher;
use MakiseCo\Middleware\DispatcherFactory;
use MakiseCo\Middleware\MiddlewareResolver;

$dispatcher = new Dispatcher([$middleware1, $middleware2, $requestHanlder]);

// or you can use MiddlewareResolver with PsrContainer implementation to resolve middlewares
$dispatcher = new Dispatcher(
    [Middleware1::class, Middleware2::class, RequestHandler::class],
    new MiddlewareResolver($container)
);

// or you can use Dispatcher factory (with optional MiddlewareResolver)
$factory = new DispatcherFactory(new MiddlewareResolver($container));
$dispatcher = $factory->create([Middleware1::class, Middleware2::class, RequestHandler::class]);

$response = $dispatcher->handle($request);

```

### MiddlewarePipe (Linked List)

#### Creating middleware pipeline
```php
<?php

declare(strict_types=1);

use MakiseCo\Middleware\MiddlewarePipeFactory;
use MakiseCo\Middleware\MiddlewareResolver;

$factory = new MiddlewarePipeFactory();
$pipeline = $factory->create([$middleware1, $middleware2, $requestHanlder]);

// or you can use MiddlewareResolver with PsrContainer implementation to resolve middlewares
$factory = new MiddlewarePipeFactory(new MiddlewareResolver($container));
$pipeline = $factory->create([Middleware1::class, MIddleware2::class, RequestHandler::class]);

$response = $pipeline->handle($request);
```

Each pipeline must end with Response producer, otherwise pipeline will fail with RuntimeException (Empty handler)

#### Merging pipelines
```php
<?php

declare(strict_types=1);

use MakiseCo\Middleware\MiddlewarePipeFactory;

$factory = new MiddlewarePipeFactory();
$subPipeline = $factory->create([$middleware1_1, $middleware1_2]);
$pipeline = $factory->create([$middleware1, $subPipeline, $middleware2, $requestHanlder]);
```

Execution order will be: middleware1 -> middleware1_1 -> middleware1_2 -> middleware2 -> requestHandler

#### Adding error handling middleware
According to the [PSR-15](https://www.php-fig.org/psr/psr-15/#14-handling-exceptions) error handling middleware must be a first middleware in a pipeline.

```php
<?php

declare(strict_types=1);

use MakiseCo\Middleware\ErrorHandlingMiddleware;
use MakiseCo\Middleware\MiddlewarePipeFactory;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpErrorHandler implements \MakiseCo\Middleware\ErrorHandlerInterface
{
    private ResponseFactoryInterface $responseFactory;
    private LoggerInterface $logger;
    private bool $debug;

    public function __construct(ResponseFactoryInterface $responseFactory, LoggerInterface $logger, bool $debug)
    {
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
        $this->debug = $debug;
    }

    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Write own error handler
        $this->logger->error(...);

        if ($this->debug) {
            // TODO: Create detailed error response
        }
        
        return $this->responseFactory->createResponse(500);
    }
}

$errorHandlingMiddleware = new ErrorHandlingMiddleware(
    new HttpErrorHandler($responseFactory, $logger, true)
);

$factory = new MiddlewarePipeFactory();
$pipeline = $factory->create([
    $errorHandlingMiddleware,
    $middleware1,
    $middleware2,
    $requestHandler,
    // ...
]);

$response = $pipeline->handle($request);
```
