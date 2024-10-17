<?php

namespace Ilias\Choir\Model\Transport;

use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;

final class Request extends TrackableTable
{
  public Transport $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $userId;
  /** @not_nuable */
  public string $origin;
  /** @not_nuable */
  public string $destination;
  /** @not_nuable */
  public Timestamp $desiredDate;

  public function compose()
  {
  }

  public static function validateInsert(array $params): array
  {
    $arErr = [];
    if (!isset($params["origin"]) || empty($params["origin"])) {
      $arErr["origin"] = "request_origin_required_message";
    }
    if (!isset($params["destination"]) || empty($params["destination"])) {
      $arErr["destination"] = "request_destination_required_message";
    }
    return $arErr;
  }

  public static function validateUpdate(array $params): array
  {
    $arErr = self::validateInsert($params);
    if (!isset($params["id"]) || empty($params["id"])) {
      $arErr["id"] = "request_id_required_message";
    }
    return $arErr;
  }
}
