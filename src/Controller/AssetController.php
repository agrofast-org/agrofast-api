<?php

namespace Ilias\Choir\Controller;

use Ilias\Opherator\Response;
use Ilias\Rhetoric\Router\Router;

class AssetController
{
  public static function instruction()
  {
    Response::setResponse([
      "instructions" => [
        "introduction" => "Instructions on how do get assets",
        "step-by-step" => "There is a placeholder :p"
      ],
    ]);
  }

  public static function getAssetByName()
  {
    DebugController::showEnvironment();

    // Request::$response = [
    //   "params" => Request::$params
    // ];
  }

  public static function getAssetById()
  {
    Response::setResponse([
      "params" => Router::getParams()
    ]);
  }
}