<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Hr\AuthCode;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;
use Ilias\Opherator\Response;
use Throwable;

class UserController
{
  public static function getUser()
  {
    $params = Request::getQuery();
    if (isset($params['id']) || isset($params['telephone']) || isset($params['name'])) {
      $select = new Select();
      $select->from(['u' => User::class]);
      $conditions = [];
      $conditionsLike = [];
      if (isset($params['id'])) {
        $conditions['u.id'] = (int) $params['id'];
      } elseif (isset($params['telephone'])) {
        $conditionsLike['u.number'] = $params['telephone'];
      } elseif (isset($params['name'])) {
        $conditionsLike["u.name"] = $params['name'];
      }
      $select->where($conditions, Query::OR , Query::EQUALS);
      $select->where($conditionsLike, Query::OR , Query::LIKE);

      try {
        $result = $select->execute();
        if (!empty($result)) {
          return new JsonResponse(new StatusCode(StatusCode::OK), ['data' => $result[0]]);
        }
        return new JsonResponse(new StatusCode(StatusCode::NOT_FOUND), ['message' => 'User not found']);
      } catch (\Exception $e) {
        return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'An unexpected error has occurred']);
      }
    }
    return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'No user information provided']);
  }

  public static function createUser()
  {
    $params = Request::getBody();
    $user = User::fetchRow(['number' => $params['number']]);

    if ($user) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'User already exists']);
    }
    try {
      $user = new User($params['name'], $params['number'], $params['password'], true, new Timestamp());
      $user->authenticated = false;
      $insert = new Insert();
      $insert->into(User::class)
        ->values($user)
        ->returning(['*']);

      $transaction = new Transaction();
      $transaction->begin();

      $result = $insert->execute()[0];
      if ($result) {
        $insert = new Insert();
        $insert->into(AuthCode::class)
          ->values(['user_id' => $result['id']])
          ->returning(['id']);
        $authCodeId = $insert->execute()[0]['id'];
        $transaction->commit();
        return new JsonResponse(new StatusCode(StatusCode::CREATED), ['message' => 'Auth code sent']);
      }
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::CREATED), ['message' => 'Could not create user', 'data' => $result]);
    } catch (Throwable $t) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Failed to create user', 'error' => $t->getMessage(), 'code' => $params]);
    }
  }

  public static function authenticateUser()
  {
    $params = Request::getBody();
  }
}