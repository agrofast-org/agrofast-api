<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Model\Transport\Carrier;
use Ilias\Choir\Model\Transport\Offer;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;

class OfferController
{
  public static function listOffers()
  {
    $user = User::getAuthenticatedUser();
    $select = new Select();
    $offers = $select->from(['t' => Offer::class])
      ->where(['t.user_id' => $user->id])
      ->execute();
    return new JsonResponse(new StatusCode(StatusCode::OK), $offers);
  }

  public static function makeOffer()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    $arErr = Carrier::validateInsert($params);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields', 'fields' => $arErr]);
    }
    $offer = Offer::fetchRow(['user_id'=> $user->id, 'active'=> true]);
    if (!empty($offer)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message'=> 'Cannot make more than one offer at a time']);
    }
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $insert = new Insert();
      $insert->into(Offer::class)
        ->values([
          'user_id' => $user->id,
          'request_id' => $params['request_id'],
          'carrier_id' => $params['carrier_id'],
          'float' => $params['float'],
        ]);
      $insert->execute();
      $transaction->commit();

      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'Request created']);
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to make request']);
    }
  }

  public static function updateOffer()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    $arErr = Offer::validateUpdate($params);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields', 'fields' => $arErr]);
    }
    $offer = Offer::fetchRow(['id' => $params['id'], 'user_id' => $user->id]);
    if (empty($offer)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Offer not found']);
    }

    $transaction = new Transaction();
    $transaction->begin();
    try {
      $update = new Update();
      $update->table(Offer::class)
        ->set([
          'request_id' => $params['request_id'],
          'carrier_id' => $params['carrier_id'],
          'float' => $params['float'],
        ])
        ->where(['id' => $params['id']]);
      $update->execute();
      $transaction->commit();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message'=> 'Offer updated']);
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to update offer']);
    }
  }

  public static function cancelOffer()
  {
    $user = User::getAuthenticatedUser();
    $params = Request::getBody();
    if (empty($params['id'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Missing required fields']);
    }
    $offer = Offer::fetchRow(['id' => $params['id'], 'user_id' => $user->id]);
    if (empty($offer)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Request not found']);
    }
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $update = new Update();
      $update->table(Offer::class)
        ->set(['active' => false])
        ->where(['id' => $params['id']]);
      $update->execute();
      $transaction->commit();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message'=> 'Offer cancelled']); 
    } catch (\Throwable) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to cancel offer']);
    }
  }
}
