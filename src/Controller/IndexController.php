<?php

namespace Ilias\Choir\Controller;

use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Response;
use Ilias\Choir\Model\Asset;
use Ilias\Opherator\Request\StatusCode;

class IndexController
{
  public static function handleApiIndex()
  {
    $response = new JsonResponse(new StatusCode(StatusCode::OK), [
      "message" => 'Welcome to the Agrofast API',
    ]);
    return $response;
  }

  public static function favicon()
  {
    $assetLoader = new Asset();
    $assetLoader->loadAsset("img", "favicon.ico");
  }
}
