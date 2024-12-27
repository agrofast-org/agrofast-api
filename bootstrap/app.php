<?php

use App\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/index.php',
        apiPrefix: '/',
    )
    ->withMiddleware(
        function (Middleware $middleware) {
            $app = Application::getInstance();
            $router = $app->make('router');
            $kernel = new Kernel($app, $router);

            $kernelAliases = $kernel->getMiddlewareAliases();
            $middleware->alias(
                $kernelAliases
            );
        }
    )
    ->withExceptions(
        //
    )->create();
