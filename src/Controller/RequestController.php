<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Model\Transport\Request as TransportRequest;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;

class RequestController
{
  public static function listRequests()
  {
    $user = User::getAuthenticatedUser();
    $select = new Select();
    $result = $select->from(['t' => TransportRequest::class])
      ->where(['t.user_id' => $user->id])
      ->execute();
    return new JsonResponse(new StatusCode(StatusCode::OK), ['data' => $result]);
  }

  public static function makeRequest()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    $arErr = TransportRequest::validateInsert($params);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields', 'fields' => $arErr]);
    }
    $request = TransportRequest::fetchRow(['user_id'=> $user->id, 'active'=> true]);
    if (!empty($request)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Cannot make more than one request at a time']);
    }

    $transaction = new Transaction();
    $transaction->begin();
    try {
      $insert = new Insert();
      $insert->into(TransportRequest::class)
        ->values([
          'user_id' => $user->id,
          'origin' => $params['origin'],
          'destination' => $params['destination'],
        ]);
      $insert->execute();
      $transaction->commit();

      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'Request created']);
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to make request']);
    }
  }

  public static function updateRequest()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    $arErr = TransportRequest::validateUpdate($params);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields', 'fields' => $arErr]);
    }
    $request = TransportRequest::fetchRow(['id' => $params['id'], 'user_id' => $user->id]);
    if (empty($request)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Request not found']);
    }

    $transaction = new Transaction();
    $transaction->begin();
    try {
      $update = new Update();
      $update->table(TransportRequest::class)
        ->set([
          'origin' => $params['origin'],
          'destination' => $params['destination'],
          'updated_in' => new Timestamp()
        ])
        ->where(['id' => $params['id']]);
      $update->execute();
      $transaction->commit();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message'=> 'Request updated']);
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to update request']);
    }
  }

  public static function cancelRequest()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    if (empty($params['id'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields']);
    }
    $request = TransportRequest::fetchRow(['id' => $params['id'], 'user_id' => $user->id]);
    if (empty($request)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Request not found']);
    }
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $update = new Update();
      $update->table(TransportRequest::class)
        ->set(['active' => false])
        ->where(['id' => $params['id']]);
      $update->execute();
      $transaction->commit();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message'=> 'Request cancelled']); 
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to cancel request']);
    }
  }
}
