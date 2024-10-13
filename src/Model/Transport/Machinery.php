<?php

namespace Ilias\Choir\Model\Transport;

use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Choir\Model\Vehicle;
use Ilias\Maestro\Types\Serial;

final class Machinery extends Vehicle
{
  public Transport $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */

  public function compose()
  {
  }

  public static function validateInsert(array $params): array
  {
    $arErr = [];
    if (!isset($params["name"]) || empty($params["name"])) {
      $arErr["name"] = "machinery_name_required_message";
    }
    if (!isset($params["model"]) || empty($params["model"])) {
      $arErr["model"] = "machinery_model_required_message";
    }
    if (!isset($params["plate"]) || empty($params["plate"])) {
      $arErr["plate"] = "machinery_plate_required_message";
    }
    return $arErr;
  }

  public static function validateUpdate(array $params): array
  {
    return self::validateInsert($params);
  }
}
