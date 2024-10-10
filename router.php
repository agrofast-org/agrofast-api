<?php

use Ilias\Choir\Controller\DebugController;
use Ilias\Choir\Controller\IndexController;
use Ilias\Choir\Controller\UserController;
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

Router::group("/user", function ($router) {
  $router->get("/", UserController::class . "@getUser");
  $router->post("/", UserController::class . "@createUser");
  $router->post("/auth", UserController::class . "@authenticateUser", [new JwtMiddleware()]);
  $router->post("/login", UserController::class . "@userLogin");
}, []);
