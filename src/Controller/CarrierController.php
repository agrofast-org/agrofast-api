<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Model\Transport\Carrier;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;

class CarrierController
{
  public static function listTransports()
  {
    $user = User::getAuthenticatedUser();
    $select = new Select();
    $result = $select->from(['m' => Carrier::class])
      ->where(['m.user_id' => $user->id])
      ->execute();
    return $result;
  }

  public static function createTransport()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    if (empty($params['name']) || empty($params['model']) || empty($params['plate'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields']);
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
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to create machine']);
    }
  }

  public static function updateTransport()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    if (empty($params['id']) && empty($params['name']) && empty($params['model']) && empty($params['plate'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Nothing to update']);
    }
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $update = new Update();
      $update->table(Carrier::class)
        ->set([
          'name' => $params['name'],
          'model' => $params['model'],
          'plate' => $params['plate']
        ])
        ->where(['id' => $params['id']]);
      $update->execute();
      $transaction->commit();
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to update machine']);
    }
  }

  public static function disableTransport()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    if (empty($params['id'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields']);
    }
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $update = new Update();
      $update->table(Carrier::class)
        ->set(['active' => true])
        ->where(['id' => $params['id']]);
      $update->execute();
      $transaction->commit();
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to disable machine']);
    }
  }
}
