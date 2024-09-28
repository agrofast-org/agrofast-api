<?php

namespace Ilias\Choir\Controller;

use Ilias\Opherator\Response;
use Ilias\Choir\Model\Asset;

class IndexController
{
  public static function handleApiIndex()
  {
    Response::appendResponse("message", 'Welcome to the Agrofast API');
  }

  public static function favicon()
  {
    $assetLoader = new Asset();
    $assetLoader->loadAsset("img", "favicon.ico");
  }
}
