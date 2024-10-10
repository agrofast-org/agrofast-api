<?php

use Ilias\Choir\Controller\DebugController;
use Ilias\Choir\Controller\IndexController;
use Ilias\Choir\Controller\MachineryController;
use Ilias\Choir\Controller\RequestController;
use Ilias\Choir\Controller\TransportController;
use Ilias\Choir\Controller\UserController;
use Ilias\Choir\Middleware\AuthUserMiddleware;
use Ilias\Choir\Middleware\EnvironmentMiddleware;
use Ilias\Choir\Middleware\JwtMiddleware;
use Ilias\Rhetoric\Router\Router;

Router::get("/", IndexController::class . "@handleApiIndex");
Router::get("/favicon.ico", IndexController::class . "@favicon");

Router::group("/debug", function ($router) {
  $router->get("/", DebugController::class . "@showEnvironment");
  $router->group("/env", function ($router) {
    $router->get("/", DebugController::class . "@getEnvironmentInstructions");
    $router->get("/{variable}", DebugController::class . "@getEnvironmentVariable");
  });
  $router->get("/dir", DebugController::class . "@mapProjectFiles");
  $router->get("/file", DebugController::class . "@getFileContent");
  $router->post("/body", DebugController::class . "@showBody");
}, [new EnvironmentMiddleware()]);

// User routes

Router::group("/user", function ($router) {
  $router->get("/", UserController::class . "@getUser");
  $router->post("/", UserController::class . "@createUser");
  $router->post("/auth", UserController::class . "@authenticateUser", [new JwtMiddleware()]);
  $router->post("/login", UserController::class . "@userLogin");
}, []);

// Machinery routes

Router::group("/machinery", function ($router) {
  $router->get("/", MachineryController::class . "@listMachinery");
  $router->post("/create", MachineryController::class . "@createMachine");
  $router->put("/update", MachineryController::class . "@updateMachine");
  $router->delete("/disable", MachineryController::class . "@disableMachine");
}, [new AuthUserMiddleware()]);

// Transport vehicle routes

Router::group("/transport", function ($router) {
  $router->get("/", TransportController::class . "@listTransports");
  $router->post("/create", TransportController::class . "@createTransport");
  $router->put("/update", MachineryController::class . "@updateTransport");
  $router->delete("/disable", MachineryController::class . "@disableTransport");
}, [new AuthUserMiddleware()]);

// Requests routes

Router::group("/request", function ($router) {
  $router->post("/", RequestController::class . "@listRequests");
  $router->post("/create", RequestController::class . "@makeRequest");
  $router->put("/update", MachineryController::class . "@updateRequest");
  $router->delete("/cancel", MachineryController::class . "@cancelRequest");
}, [new AuthUserMiddleware()]);

