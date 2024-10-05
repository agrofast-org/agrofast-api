<?php

namespace Ilias\Choir\Bootstrap;

use Ilias\Dotenv\Environment;
use Ilias\Dotenv\Exceptions\EnvironmentNotFound;
use Ilias\Opherator\MetaData;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;
use Ilias\Opherator\Response;
use Ilias\Rhetoric\Exceptions\MiddlewareException;
use Ilias\Rhetoric\Exceptions\RouteNotFoundException;
use Ilias\Rhetoric\Router\Router;

class Core
{

  public static function handle(array $params = [])
  {
    try {
      Environment::setup(null, Environment::SUPPRESS_EXCEPTION);
      Request::setup();

      $metaData = new MetaData();
      Response::setHeader($metaData->getContentType(MetaData::CONTENT_TYPE_JSON), true);
      Router::setup();

      Response::appendResponse("status", http_response_code(), true);

      Response::answer();
    } catch (EnvironmentNotFound $environmentNotFoundEx) {
      self::handleEnvironmentException($environmentNotFoundEx);
    } catch (RouteNotFoundException $notFoundEx) {
      self::handleRouteException($notFoundEx);
    } catch (MiddlewareException $midEx) {
      self::handleMiddlewareException($midEx);
    } catch (\Throwable $th) {
      self::handleException($th);
    }
  }

  public static function handleEnvironmentException(EnvironmentNotFound $environmentNotFoundEx)
  {
    http_response_code(StatusCode::NOT_FOUND);
    Response::appendResponse("message", empty($environmentNotFoundEx->getMessage()) ? 'No environment file found' : $environmentNotFoundEx->getMessage());
    Response::appendResponse("status", http_response_code());
    Response::answer();
  }

  public static function handleRouteException(RouteNotFoundException $notFoundEx)
  {
    http_response_code(StatusCode::NOT_FOUND);
    Response::appendResponse("message", empty($notFoundEx->getMessage()) ? 'Route not found' : $notFoundEx->getMessage());
    Response::appendResponse("status", http_response_code());
    Response::answer();
  }

  public static function handleMiddlewareException(MiddlewareException $midEx)
  {
    http_response_code(StatusCode::UNAUTHORIZED);
    Response::appendResponse("message", empty($midEx->getMessage()) ? 'Route middleware terms not met' : $midEx->getMessage());
    Response::appendResponse("status", http_response_code());
    Response::answer();
  }

  public static function handleException(\Throwable $th)
  {
    http_response_code(StatusCode::INTERNAL_SERVER_ERROR);
    Response::appendResponse("message", empty($th->getMessage()) ? 'No error message provided' : $th->getMessage());
    Response::appendResponse("status", http_response_code());
    Response::appendResponse("exception", $th);
    Response::answer();
  }
}
