<?php

namespace Ilias\Choir\Bootstrap;

use Ilias\Dotenv\Environment;
use Ilias\Opherator\Request\Request;
use Ilias\Opherator\Request\Response;
use Ilias\Rhetoric\Router\Router;
use Ilias\Rhetoric\Router\Routes;

class Core
{

  public static function handle(array $params = [])
  {
    /* Request handling example */
    try {
      Environment::setup();
      Routes::setup();
      Response::jsonResponse();
      Request::setup();

      Router::setup();

      Response::appendResponse("status", http_response_code(), true);

      Response::answer();
    } catch (\Throwable $th) {
      http_response_code(500);
      self::handleException($th);
    }
  }

  public static function handleException(\Throwable $th)
  {
    Response::appendResponse("message", $th->getMessage());
    Response::appendResponse("status", http_response_code());
    Response::appendResponse("exception", get_object_vars($th));

    Response::answer();
  }
}
