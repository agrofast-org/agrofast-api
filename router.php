<?php

use Ilias\Choir\Controller\DebugController;
use Ilias\Choir\Controller\IndexController;
use Ilias\Choir\Controller\MachineryController;
use Ilias\Choir\Controller\RequestController;
use Ilias\Choir\Controller\CarrierController;
use Ilias\Choir\Controller\OfferController;
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
  $router->get("/lasterror", DebugController::class . "@getLastError");

  $router->get("/dir", DebugController::class . "@mapProjectFiles");
  $router->get("/file", DebugController::class . "@getFileContent");
  $router->post("/body", DebugController::class . "@showBody");
}, [new EnvironmentMiddleware()]);

// User routes

Router::group("/user", function ($router) {
  $router->get("/", UserController::class . "@getUser");
  $router->get("/exists", UserController::class . "@checkIfExists");
  $router->post("/", UserController::class . "@createUser");
  $router->get("/auth", UserController::class . "@authenticateUser", [new JwtMiddleware()]);
  $router->post("/login", UserController::class . "@userLogin");
  $router->get("/resend-code", UserController::class . "@resendCode", [new JwtMiddleware()]);
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
  $router->get("/", CarrierController::class . "@listTransports");
  $router->post("/create", CarrierController::class . "@createTransport");
  $router->put("/update", CarrierController::class . "@updateTransport");
  $router->delete("/disable", CarrierController::class . "@disableTransport");
}, [new AuthUserMiddleware()]);

// Requests routes

Router::group("/request", function ($router) {
  $router->get("/", RequestController::class . "@listRequests");
  $router->post("/create", RequestController::class . "@makeRequest");
  $router->put("/update", RequestController::class . "@updateRequest");
  $router->delete("/cancel", RequestController::class . "@cancelRequest");
}, [new AuthUserMiddleware()]);

// 

Router::group("/offer", function ($router) {
  $router->get("/", OfferController::class . "@listOffers");
  $router->post("/create", OfferController::class . "@makeOffer");
  $router->put("/update", OfferController::class . "@updateOffer");
  $router->delete("/cancel", OfferController::class . "@cancelOffer");
}, [new AuthUserMiddleware()]);
