<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Database\Select;

class MachineryController
{

  public static function listMachinery()
  {
    $user = User::getAuthenticatedUser();
    $select = new Select();
    $select->from(['m' => Machinery::class]);
  }

  public static function createMachine()
  {

  }

  public static function updateMachine()
  {

  }

  public static function disableMachine()
  {

  }
}
