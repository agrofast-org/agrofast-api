<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Bootstrap\Core;
use Ilias\Opherator\Request;
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
}
