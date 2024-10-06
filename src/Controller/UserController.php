<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Database\Select;
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
      $select = new Select(Maestro::SQL_STRICT, PDOConnection::get());
      $select->from(['u' => User::class]);
      $conditions = [];
      if (isset($params['id'])) {
        $conditions['u.id'] = (int) $params['id'];
      } elseif (isset($params['telephone'])) {
        $conditions['u.number'] = $params['telephone'];
      } elseif (isset($params['name'])) {
        $conditions[(string)new Expression("unaccent(u.name)")] = new Expression("unaccent({$params['name']})");
      }
      $select->where($conditions, Query::OR , Query::EQUALS);

      return new JsonResponse(new StatusCode(StatusCode::OK), ['data' => $select->execute()[0]]);
    }
    return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'No user information provided']);
  }

  public static function createUser()
  {
    $params = Request::getBody();
    try {
      $user = new User($params['name'], $params['number'], $params['password'],true, new Timestamp());
      $insert = new Insert(Maestro::SQL_STRICT, PDOConnection::get());
      $result = $insert->into(User::class)->values($user)->returning(['id'])->execute();
      return new JsonResponse(new StatusCode(StatusCode::OK), ['message' => 'User created', 'data' => $result]);
    } catch (Throwable $t) {
      return new JsonResponse(new StatusCode(StatusCode::BAD_REQUEST), ['message' => 'Failed to create user', 'error' => $t->getMessage(), 'code' => $params]);
    }
  }
}