<?php

namespace Ilias\Choir\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ilias\Choir\Model\Hr\AuthCode;
use Ilias\Choir\Model\Hr\Session;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;
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
      if ($user['authenticated'] == 'true') {
        return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'User already authenticated']);
      }
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'User already exists']);
    }
    try {
      if (empty($params['name']) || empty($params['number']) || empty($params['password'])) {
        return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Name, number, and password are required']);
      }

      $user = new User($params['name'], $params['number'], $params['password'], true, new Timestamp());
      $user->authenticated = false;
      $insert = new Insert();
      $insert->into(User::class)
        ->values($user)
        ->returning(['id', 'name', 'number']);

      $transaction = new Transaction();
      $transaction->begin();

      $result = $insert->execute()[0];
      if ($result) {
        $insert = new Insert();
        $insert->into(AuthCode::class)
          ->values(['user_id' => $result['id']])
          ->returning(['id']);
        $insert->execute()[0]['id'];
        $transaction->commit();
        $jwt = JWT::encode($result, getenv('APP_JWT_SECRET'), 'HS256');
        return new JsonResponse(new StatusCode(StatusCode::CREATED), ['message' => 'User created and auth code sent', 'token' => $jwt]);
      }
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Could not create user']);
    } catch (Throwable $t) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to create user']);
    }
  }

  public static function authenticateUser()
  {
    $query = Request::getQuery();
    $headers = Request::getHeaders();
    if (empty($query['code'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'No code provided']);
    }
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $user = JWT::decode($token, new Key(getenv('APP_JWT_SECRET'), 'HS256'));

    $transaction = new Transaction();
    $transaction->begin();

    try {
      $authCode = AuthCode::fetchRow(['user_id' => $user->id, 'code' => $query['code']]);
      if ($authCode) {
        $updateUser = new Update();
        $updateUser->table(User::class)
          ->set(['authenticated' => true])
          ->where(['id' => $user->id]);
        $updateAuthCode = new Update();
        $updateAuthCode->table(AuthCode::class)
          ->set(['active' => false])
          ->where(['user_id' => $user->id]);

        $insertSession = new Insert();
        $insertSession->into(Session::class)
          ->values(['user_id' => $user->id, 'token' => ''])
          ->returning(['id']);

        $updateUser->execute();
        $updateAuthCode->execute();
        $user->session_id = $insertSession->execute()[0]['id'];
        $token = JWT::encode((array)$user, getenv('APP_JWT_SECRET'), 'HS256');

        $transaction->commit();
        return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'User authenticated', 'token' => $token, 'data' => $user]);
      }
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'No matching code found']);
    } catch (Throwable $th) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to authenticate user']);
    }
  }
}
