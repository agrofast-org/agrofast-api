<?php

namespace Ilias\Choir\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ilias\Choir\Model\Hr\AuthCode;
use Ilias\Choir\Model\Hr\Session;
use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Utilities\Utils;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request;
use Ilias\Opherator\Request\StatusCode;
use Ilias\Rhetoric\Router\Router;

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
      $select->where($conditions, Query::OR , Query::EQUALS)
        ->where($conditionsLike, Query::OR , Query::LIKE);

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

  public static function checkIfExists()
  {
    $params = Router::getParams();
    if (empty($params['number'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'User number is required']);
    }
    $arErr = Utils::validatePhoneNumber($params['number']);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Invalid number', 'errors' => $arErr]);
    }
    $user = User::fetchRow(['number' => $params['number']]);
    if (empty($user)) {
      return new JsonResponse(new StatusCode(StatusCode::NOT_FOUND), ['message' => 'User not found']);
    }
    return new JsonResponse(new StatusCode(StatusCode::OK), ['message'=> 'User found','data'=> [
      'name' => $user->name,
      'number' => $user->number,
    ]]);
  }

  public static function createUser()
  {
    $params = Request::getBody();
    $arErr = User::validateInsert($params);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Required fields not supplied', 'fields' => $arErr]);
    }
    $user = User::fetchRow(['number' => $params['number']]);

    if ($user) {
      if ($user->authenticated == 'true') {
        return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'User already authenticated']);
      }
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'User already exists']);
    }
    try {
      $user = new User($params['name'], $params['number'], $params['password'], true, new Timestamp());
      $user->authenticated = false;
      $user->password = md5($user->password);
      $insert = new Insert();
      $insert->into(User::class)
        ->values($user)
        ->returning(['id', 'name', 'number']);

      $transaction = new Transaction();
      $transaction->begin();

      $result = $insert->execute()[0];
      if ($result) {
        $authCode = AuthCode::create($result['id']);

        $result['created_in'] = $authCode['created_in'];

        $transaction->commit();
        $jwt = JWT::encode($result, getenv('APP_JWT_SECRET'), 'HS256');
        return new JsonResponse(new StatusCode(StatusCode::CREATED), ['message' => 'User created and auth code sent', 'token' => $jwt]);
      }
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Could not create user']);
    } catch (\Throwable $t) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to create user', 'error' => $t->getMessage()]);
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
      $authCode = AuthCode::fetchRow(['user_id' => $user->id, 'code' => $query['code'], 'active' => true]);
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
          ->returning(['id', 'created_in']);

        $updateUser->execute();
        $updateAuthCode->execute();
        $session = $insertSession->execute()[0];
        $user->session_id = $session['id'];

        $jwtData = [
          'id' => $user->id,
          'name' => $user->name,
          'number' => $user->number,
          'session_id' => $user->session_id,
          'session_ts' => $session['created_in'],
        ];
        $token = JWT::encode($jwtData, getenv('APP_JWT_SECRET'), 'HS256');

        $transaction->commit();
        return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'User authenticated', 'token' => $token, 'data' => $user]);
      }
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'No matching code found']);
    } catch (\Throwable $th) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to authenticate user', 'error' => $th->getMessage()]);
    }
  }

  public static function userLogin()
  {
    $params = Request::getBody();
    if (empty($params['number']) || empty($params['password'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Number and password are required']);
    }
    $user = User::fetchRow(['number' => $params['number'], 'password' => md5($params['password'])]);
    $transaction = new Transaction();
    $transaction->begin();
    if ($user) {
      $select = new Select();
      $select->from(['a' => AuthCode::class], ['created_in'])
        ->where(['a.user_id' => $user->id], compaction: Query::EQUALS)
        ->where(['a.created_in' => new Timestamp(strtotime('-3 minutes'))], compaction: Query::LESS_THAN_OR_EQUAL)
        ->where(['a.active' => true], compaction: Query::EQUALS)
        ->order('a.created_in', Select::DESC);
      $authCode = $select->execute()[0];

      if ($authCode) {
        return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Auth code already sent', 'data' => $authCode]);
      }

      $updateUserAuthCodes = new Update();
      $updateUserAuthCodes->table(AuthCode::class)
        ->set(['active' => false])
        ->where(['user_id' => $user->id]);
      $updateUserAuthCodes->execute();

      $newAuthCode = AuthCode::create((int) $user->id);

      $transaction->commit();
      $jwtData = [
        'id' => $user->id,
        'name' => $user->name,
        'number' => $user->number,
        'created_in' => $newAuthCode['created_in'],
      ];
      $jwt = JWT::encode($jwtData, getenv('APP_JWT_SECRET'), 'HS256');
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'Login code sent', 'token' => $jwt]);
    }
    $transaction->rollback();
    return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'User not found']);
  }
}
