<?php

namespace Ilias\Choir\Model\Transport;

use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;

final class Offer extends TrackableTable
{
  public Transport $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $userId;
  /** @not_nuable */
  public Request|int $requestId;
  /** @not_nuable */
  public Carrier|int $carrierId;
  /** @not_nuable */
  public float $price;

  public function compose()
  {
  }

  public static function validateInsert(array $params): array
  {
    $arErr = [];
    if (!isset($params["user_id"]) || empty($params["user_id"])) {
      $arErr["user_id"] = "offer_user_id_required_message";
    }
    if (!isset($params["request_id"]) || empty($params["request_id"])) {
      $arErr["request_id"] = "offer_request_id_required_message";
    }
    if (!isset($params["carrier_id"]) || empty($params["carrier_id"])) {
      $arErr["carrier_id"] = "offer_carrier_id_required_message";
    }
    if (!isset($params["price"]) || empty($params["price"])) {
      $arErr["price"] = "offer_price_required_message";
    }
    return $arErr;
  }

  public static function validateUpdate(array $params): array
  {
    $arErr = [];
    if (!isset($params["id"]) || empty($params["id"])) {
      $arErr["id"] = "offer_id_required_message";
    }
    if (!isset($params["carrier_id"]) || empty($params["carrier_id"])) {
      $arErr["carrier_id"] = "offer_carrier_id_required_message";
    }
    if (!isset($params["price"]) || empty($params["price"])) {
      $arErr["price"] = "offer_price_required_message";
    }
    return $arErr;
  }
}
