<?php

use App\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        apiPrefix: '/',
        api: __DIR__.'/../routes/index.php',
        commands: __DIR__.'/../routes/console.php',
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
    )->create()
;
