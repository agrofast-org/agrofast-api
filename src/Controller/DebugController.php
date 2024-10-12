<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Bootstrap\Core;
use Ilias\Choir\Model\System\ErrorLog;
use Ilias\Maestro\Database\Select;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;
use Ilias\Opherator\Response;
use Ilias\Choir\Utilities\DirectoryReader;
use Ilias\Choir\Utilities\FileReader;
use Ilias\Rhetoric\Router\Router;

class DebugController
{
  public static function showEnvironment()
  {
    Response::appendResponse("message", ["ping" => "pong"]);
    Response::appendResponse("data", ["request" => $GLOBALS]);
    Response::appendResponse("request", [
      "request_method" => Request::getMethod(),
      "params" => Router::getParams(),
      Request::hasBody() && "body" => Request::getBody(),
      "query" => Request::getQuery(),
    ]);
  }

  public static function showNestedParams()
  {
    Response::appendResponse("request", [
      "params" => Router::getParams(),
      "query" => Request::getQuery(),
    ]);
  }

  public static function getEnvironmentInstructions()
  {
    Response::appendResponse("message", [
      "instruction" => "There is none yet."
    ]);
  }

  public static function getEnvironmentVariable()
  {
    Response::appendResponse("data", [
      "requested_var" => Router::getParams()["variable"],
      // "variable_val" => Environments::$vars[Request::$params["variable"]]
    ]);
    Response::appendResponse("message", "This functionality will not return values.");
  }

  public static function mapProjectFiles()
  {
    $directoryReader = new DirectoryReader($_SERVER['DOCUMENT_ROOT']);
    Response::appendResponse("data", $directoryReader->readDirectory());
  }

  public static function getFileContent()
  {
    $filePath = Request::getQuery()["path"];
    $directoryReader = new FileReader($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $filePath);
    Response::appendResponse("data", $directoryReader->readFile());
  }

  public static function showBody()
  {
    Response::appendResponse("data", Request::getBody());
  }

  public static function getLastError()
  {
    $select = new Select();
    $select->from(['e' => ErrorLog::class])
      ->order('created_in', 'DESC')
      ->limit(1);
    $result = $select->execute()[0];
    if ($result) {
      $result['json'] = json_decode($result['json']);
      $result['params'] = json_decode($result['params']);
      return new JsonResponse(new StatusCode(StatusCode::OK), ['data' => $result]);
    }
    return new JsonResponse(new StatusCode(StatusCode::OK), ['data'=> null]);
  }
}
