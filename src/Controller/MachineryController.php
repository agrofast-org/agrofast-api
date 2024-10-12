<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Model\Transport\Machinery;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;

class MachineryController
{

  public static function listMachinery()
  {
    $user = User::getAuthenticatedUser();
    $select = new Select();
    $result = $select->from(['m' => Machinery::class])
      ->where(['m.user_id' => $user->id])
      ->execute();
    return new JsonResponse(new StatusCode(StatusCode::OK), ['data' => $result]);
  }

  public static function createMachine()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    $arErr = Machinery::validateInsert($params);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields', 'fields' => $arErr]);
    }
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $insert = new Insert();
      $insert->into(Machinery::class)
        ->values([
          'user_id' => $user->id,
          'name' => $params['name'],
          'model' => $params['model'],
          'plate' => $params['plate']
        ]);
      $insert->execute();
      $transaction->commit();

      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'Machine created']);
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to create machine']);
    }
  }

  public static function updateMachine()
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
      $update->table(Machinery::class)
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

  public static function disableMachine()
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
      $update->table(Machinery::class)
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
