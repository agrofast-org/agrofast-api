<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Model\Transport\Carrier;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;

class CarrierController
{
  public static function listTransports()
  {
    $user = User::getAuthenticatedUser();
    $select = new Select();
    $result = $select->from(['c' => Carrier::class])
      ->where(['c.user_id' => $user->id])
      ->execute();
    return new JsonResponse(new StatusCode(StatusCode::OK), ['data' => $result]);
  }

  public static function createTransport()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    $arErr = Carrier::validateInsert($params);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields', 'fields' => $arErr]);
    }

    $carriers = Carrier::fetchRow(['plate' => $params['plate'], 'active' => true]);
    if (!empty($carriers)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Transport with this plate already exists']);
    }

    $transaction = new Transaction();
    $transaction->begin();
    try {
      $insert = new Insert();
      $insert->into(Carrier::class)
        ->values([
          'user_id' => $user->id,
          'name' => $params['name'],
          'model' => $params['model'],
          'plate' => $params['plate']
        ]);
      $insert->execute();
      $transaction->commit();

      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'Carrier created']);
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to create carrier']);
    }
  }

  public static function updateTransport()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    $arErr = Carrier::validateUpdate($params);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields', 'fields' => $arErr]);
    }
    $carrier = Carrier::fetchRow(['id' => $params['id'], 'user_id' => $user->id]);
    if (empty($carrier)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Carrier not found']);
    }

    $transaction = new Transaction();
    $transaction->begin();
    try {
      $update = new Update();
      $update->table(Carrier::class)
        ->set([
          'name' => $params['name'],
          'model' => $params['model'],
          'plate' => $params['plate'],
          'updated_in' => new Timestamp()
        ])
        ->where(['id' => $params['id']]);
      $update->execute();
      $transaction->commit();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message'=> 'Carrier updated']);
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to update carrier']);
    }
  }

  public static function disableTransport()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    if (empty($params['id'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields']);
    }
    $carrier = Carrier::fetchRow(['id' => $params['id'], 'user_id' => $user->id]);
    if (empty($carrier)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Carrier not found']);
    }
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $update = new Update();
      $update->table(Carrier::class)
        ->set(['active' => false])
        ->where(['id' => $params['id']]);
      $update->execute();
      $transaction->commit();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message'=> 'Carrier disabled']); 
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to disable carrier']);
    }
  }
}
