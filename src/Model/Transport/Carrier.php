<?php

namespace Ilias\Choir\Model\Transport;

use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Model\Vehicle;
use Ilias\Maestro\Types\Serial;

final class Carrier extends Vehicle
{
  public Transport $schema;
  /** @primary */
  public Serial|int $id;

  public static function validateInsert(array $params): array
  {
    $arErr = [];
    if (!isset($params["name"]) || empty($params["name"])) {
      $arErr["name"] = "carrier_name_required_message";
    }
    if (!isset($params["model"]) || empty($params["model"])) {
      $arErr["model"] = "carrier_model_required_message";
    }
    if (!isset($params["plate"]) || empty($params["plate"])) {
      $arErr["plate"] = "carrier_plate_required_message";
    }
    return $arErr;
  }

  public static function validateUpdate(array $params): array
  {
    $arErr = self::validateInsert($params);
    if (!isset($params["id"]) || empty($params["id"])) {
      $arErr["id"] = "carrier_id_required_message";
    }
    return $arErr;
  }
}
