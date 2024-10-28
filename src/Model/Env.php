<?php

namespace Ilias\Choir\Model;

class Env
{
  public static function isDev()
  {
    if (in_array(getenv("ENVIRONMENT"), ["test", "development"])) {
      return true;
    }
    return false;
  }
}
