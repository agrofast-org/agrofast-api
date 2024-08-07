<?php

namespace Ilias\Choir\Bootstrap;

use Ilias\Dotenv\Environment;
use Ilias\Opherator\Request\Request;
use Ilias\Opherator\Request\Response;
use Ilias\Rhetoric\Exceptions\MiddlewareException;
use Ilias\Rhetoric\Exceptions\RouteNotFoundException;
use Ilias\Rhetoric\Router\Router;

class Core
{

  public static function handle(array $params = [])
  {
    try {
      Environment::setup();
      Request::setup();
      Response::setJson();

      Router::setup();

      Response::appendResponse("status", http_response_code(), true);

      echo Response::answer();
    } catch (RouteNotFoundException $notFoundEx) {
      self::handleRouteException($notFoundEx);
    } catch (MiddlewareException $midEx) {
      self::handleMiddlewareException($midEx);
    } catch (\Throwable $th) {
      self::handleException($th);
    }
  }

  public static function handleRouteException(RouteNotFoundException $notFoundEx)
  {
    http_response_code(404);
    Response::appendResponse("message", $notFoundEx->getMessage());
    Response::appendResponse("status", http_response_code());
    Response::answer();
  }

  public static function handleMiddlewareException(MiddlewareException $midEx)
  {
    http_response_code(401);
    Response::appendResponse("message", $midEx->getMessage());
    Response::appendResponse("status", http_response_code());
    Response::answer();
  }

  public static function handleException(\Throwable $th)
  {
    http_response_code(500);
    Response::appendResponse("message", $th->getMessage());
    Response::appendResponse("status", http_response_code());
    Response::appendResponse("exception", $th);
    Response::answer();
  }
}
