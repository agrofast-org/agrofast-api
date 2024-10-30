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
        $nameSearch = implode("|", explode(' ', strtolower($params['name'])));
        $conditionsLike["u.name"] = $nameSearch;
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

  public static function getUserInfo()
  {
    $user = User::getAuthenticatedUser();
    if (!$user) {
      return new JsonResponse(new StatusCode(StatusCode::UNAUTHORIZED), ['message' => 'User not authenticated']);
    }
    return new JsonResponse(new StatusCode(StatusCode::OK), ['data' => $user]);
  }

  public static function checkIfExists()
  {
    $params = Request::getQuery();
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
    return new JsonResponse(new StatusCode(StatusCode::OK), [
      'message' => 'User found',
      'data' => [
        'name' => $user->name,
        'number' => $user->number,
      ]
    ]);
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
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $user = new User(trim($params['name']), $params['number'], $params['password'], true, new Timestamp());
      $user->surname = trim($params['surname']);
      $user->authenticated = false;
      $user->password = md5($user->password);
      $insert = new Insert();
      $insert->into(User::class)
        ->values($user)
        ->returning(['id', 'name', 'number']);


      $result = $insert->execute()[0];
      if ($result) {
        $authCode = AuthCode::create($result['id']);

        $result['created_in'] = $authCode['created_in'];

        $transaction->commit();
        $jwt = JWT::encode($result, getenv('APP_JWT_SECRET'), 'HS256');
        return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'User created and auth code sent', 'token' => $jwt]);
      }
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Could not create user']);
    } catch (\Throwable $t) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to create user', 'error' => $t->getMessage()]);
    }
  }

  public static function updateUser()
  {
    $headers = Request::getHeaders();
    $body = Request::getBody();
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $userFromToken = JWT::decode($token, new Key(getenv('APP_JWT_SECRET'), 'HS256'));
    $user = User::fetchRow(['id' => $userFromToken->id]);
    if (empty($user)) {
      return new JsonResponse(new StatusCode(StatusCode::NOT_FOUND), ['message' => 'User not found']);
    }
    $arErr = User::validateUpdate($body);
    if (!empty($arErr)) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Required fields not supplied', 'fields' => $arErr]);
    }
    $update = new Update();
    $update->table(User::class)
      ->set($body)
      ->where(['id' => $user->id]);

    try {
      $update->execute();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'User updated successfully']);
    } catch (\Exception $e) {
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to update user', 'error' => $e->getMessage()]);
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

  public function resendCode()
  {
    $headers = Request::getHeaders();
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $userFromToken = JWT::decode($token, new Key(getenv('APP_JWT_SECRET'), 'HS256'));

    $user = User::fetchRow(['id' => $userFromToken->id]);
    if (empty($user)) {
      return new JsonResponse(new StatusCode(StatusCode::NOT_FOUND), ['message' => 'User not found']);
    }
    $select = new Select();
    $select->from(['a' => AuthCode::class])
      ->where(['a.user_id' => $userFromToken->id], compaction: Query::EQUALS)
      ->where(['a.created_in' => (new Timestamp())->setTimestamp(strtotime('-3 minutes'))], compaction: Query::GREATER_THAN_OR_EQUAL)
      ->where(['a.active' => true], compaction: Query::EQUALS)
      ->order('a.created_in', Select::DESC);

    $result = $select->execute()[0];
    if (!empty($result)) {
      AuthCode::send($user->number, $result['code']);
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'Code resent', 'code' => 'same']);
    }

    $transaction = new Transaction();
    $transaction->begin();
    try {
      $updateUserAuthCodes = new Update();
      $updateUserAuthCodes->table(AuthCode::class)
        ->set(['active' => false])
        ->where(['user_id' => $userFromToken->id]);
      $updateUserAuthCodes->execute();

      $newAuthCode = AuthCode::create($userFromToken->id);
      AuthCode::send($user->number, $newAuthCode['code']);

      $transaction->commit();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'Code resent', 'code' => 'new']);
    } catch (\Throwable $th) {
      $transaction->rollback();
      return new JsonResponse(new StatusCode(StatusCode::INTERNAL_SERVER_ERROR), ['message' => 'Failed to resend code', 'error' => $th->getMessage()]);
    }
  }

  public static function userLogin()
  {
    $params = Request::getBody();
    if (empty($params['number']) || empty($params['password'])) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Number and password are required']);
    }
    $user = User::fetchRow(['number' => $params['number']]);
    $transaction = new Transaction();
    $transaction->begin();
    if ($user) {
      if ($user->password != md5($params['password'])) {
        $transaction->rollback();
        return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Wrong password', 'error' => 'invalid_password']);
      }
      $select = new Select();
      $select->from(['a' => AuthCode::class], ['created_in'])
        ->where(['a.user_id' => $user->id], compaction: Query::EQUALS)
        ->where(['a.created_in' => (new Timestamp())->setTimestamp(strtotime('-3 minutes'))], compaction: Query::GREATER_THAN_OR_EQUAL)
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
