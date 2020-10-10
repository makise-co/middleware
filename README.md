# PSR-15 Request dispatcher

This package provides a high performance PSR-15 request dispatcher

## Requirements
* PHP >= 7.4

## Usage
### Creating middleware pipeline
```php
<?php

declare(strict_types=1);

use MakiseCo\Middleware\MiddlewarePipelineBuilder;
use MakiseCo\Middleware\MiddlewarePipelineFactory;

$factory = new MiddlewarePipelineFactory();
$pipeline = $factory->create($requestHanlder, [$middleware1, $middleware2]);

// or

$builder = new MiddlewarePipelineBuilder($requestHanlder);
$builder->add($middleware1);
$builder->add($middleware2);
// or
$builder->addMany([$middleware1, $middleware2]);

$pipeline = $builder->build();

$response = $pipeline->handle($request);
```

### Adding error handling middleware
According to the [PSR-15](https://www.php-fig.org/psr/psr-15/#14-handling-exceptions) error handling middleware must be a first middleware in a pipeline.

```php
<?php

declare(strict_types=1);

use MakiseCo\Middleware\ErrorHandlingMiddleware;
use MakiseCo\Middleware\MiddlewarePipelineFactory;
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

$factory = new MiddlewarePipelineFactory();
$pipeline = $factory->create($requestHandler, [
    $errorHandlingMiddleware,
    $middleware1,
    $middleware2,
    // ...
]);

$response = $pipeline->handle($request);
```
