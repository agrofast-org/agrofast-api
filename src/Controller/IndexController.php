<?php

namespace Ilias\Choir\Controller;

use Ilias\Opherator\Request\Response;
use Ilias\Choir\Model\Asset;
use Ilias\Choir\Model\Greed;

class IndexController
{
  public static function handleApiIndex()
  {
    $greedModel = new Greed();
    Response::appendResponse("message", $greedModel->greed());
  }

  public static function favicon()
  {
    $assetLoader = new Asset();
    $assetLoader->loadAsset("img", "favicon.ico");
  }
}
