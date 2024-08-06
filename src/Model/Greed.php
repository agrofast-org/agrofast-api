<?php

namespace Ilias\Choir\Model;

use Ilias\Opherator\Request\Request;

class Greed
{
  public function greed()
  {
    if (isset(Request::getQuery()["uwu"])) {
      return "Good programming senpai!";
    }
    return "Good programming!";
  }
}