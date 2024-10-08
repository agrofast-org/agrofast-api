<?php

namespace Ilias\Choir\Bootstrap;

use Ilias\Choir\Model\System\ErrorLog;
use Ilias\Dotenv\Environment;
use Ilias\Dotenv\Exceptions\EnvironmentNotFound;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Opherator;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;
use Ilias\Opherator\Response;
use Ilias\Rhetoric\Exceptions\MiddlewareException;
use Ilias\Rhetoric\Exceptions\RouteNotFoundException;
use Ilias\Rhetoric\Router\Router;

class Core
{
  private static array $errors = [];
  public static function handle(array $params = [])
  {
    try {
      Environment::setup(null, Environment::SUPPRESS_EXCEPTION);
      Opherator::toggleResponseExceptions();
      Request::setup();

      Response::jsonResponse();
      Response::setResponse(Router::handle() ?? []);
    } catch (EnvironmentNotFound $environmentNotFoundEx) {
      self::handleEnvironmentException($environmentNotFoundEx);
    } catch (RouteNotFoundException $notFoundEx) {
      self::handleRouteException($notFoundEx);
    } catch (MiddlewareException $midEx) {
      self::handleMiddlewareException($midEx);
    } catch (\Throwable $th) {
      self::handleException($th);
    }
    self::dispatch();
  }

  public static function errorHandler($errno, $errstr, $errfile, $errline)
  {
    self::$errors[] = [
      'type' => $errno,
      'message' => $errstr,
      'file' => $errfile,
      'line' => $errline
    ];
    return false;
  }

  public static function dispatch()
  {
    Response::answer();
    if (!empty(self::$errors)) {
      $error = new ErrorLog(
        json_encode(self::$errors),
        json_encode(['query' => Request::getQuery(), 'body' => Request::getBody()]),
        new Timestamp()
      );
      $insert = new Insert();
      $insert->into($error::class)
        ->values($error)
        ->execute();
    }
  }

  public static function handleEnvironmentException(EnvironmentNotFound $environmentNotFoundEx): void
  {
    $response = new JsonResponse(new StatusCode(StatusCode::NOT_FOUND), [
      'message' => empty($environmentNotFoundEx->getMessage()) ? 'No environment file found' : $environmentNotFoundEx->getMessage()
    ]);
    Response::setResponse($response);
  }

  public static function handleRouteException(RouteNotFoundException $notFoundEx): void
  {
    $response = new JsonResponse(new StatusCode(StatusCode::NOT_FOUND), [
      'message' => empty($notFoundEx->getMessage()) ? 'Route not found' : $notFoundEx->getMessage()
    ]);
    Response::setResponse($response);
  }

  public static function handleMiddlewareException(MiddlewareException $midEx): void
  {
    $response = new JsonResponse(new StatusCode(StatusCode::UNAUTHORIZED), [
      'message' => empty($midEx->getMessage()) ? 'Route middleware terms not met' : $midEx->getMessage()
    ]);
    Response::setResponse($response);
  }

  public static function handleException(\Throwable $th): void
  {
    $response = new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), [
      'message' => empty($th->getMessage()) ? 'No error message provided' : $th->getMessage(),
      'exception' => $th
    ]);
    Response::setResponse($response);
  }
}
