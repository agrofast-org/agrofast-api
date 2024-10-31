<?php

namespace Ilias\Choir\Model\Hr;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Choir\Exceptions\AuthorizationNotProvidedException;
use Ilias\Choir\Exceptions\UserNotAuthorizedException;
use Ilias\Choir\Exceptions\UserNotFoundException;
use Ilias\Choir\Utilities\Utils;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Opherator\Request;
use stdClass;

final class User extends TrackableTable
{
  public Hr $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public string $name;
  /** @unique */
  /** @not_nuable */
  public string $surname;
  public string $profile_picture;
  public string $number;
  public string $email = null;
  public string $password;
  public bool $authenticated = false;

  private static null|stdClass|User $user = null;

  public function __construct(string $name, string $number, string $password, bool $active = true, $createdIn = new Timestamp())
  {
    $this->name = $name;
    $this->number = $number;
    $this->password = $password;
    $this->active = $active;
    $this->createdIn = $createdIn;
  }

  public function compose(
    string $name,
    string $number,
    string $password,
    bool $active,
    Timestamp $createdIn
  ) {
    $this->name = $name;
    $this->number = $number;
    $this->password = $password;
    $this->active = $active;
    $this->createdIn = $createdIn;
  }

  public static function validateInsert(array $params): array
  {
    $arErr = [];
    if (!isset($params["name"]) || empty($params["name"])) {
      $arErr["name"] = "user_name_required_message";
    }
    if (!isset($params["surname"]) || empty($params["surname"])) {
      $arErr["surname"] = "user_surname_required_message";
    }
    $numberErr = Utils::validatePhoneNumber($params["number"]);
    if (!empty($numberErr)) {
      $arErr["number"] = $numberErr;
    }
    $passwordErr = Utils::validatePassword($params["password"]);
    if (!empty($passwordErr)) {
      $arErr["password"] = $passwordErr;
    }
    return $arErr;
  }

  public static function validateUpdate(array $params): array
  {
    return self::validateInsert($params);
  }

  public static function getAuthenticatedUser()
  {
    if (empty(self::$user)) {
      $headers = Request::getHeaders();
      if (empty($headers['Authorization'])) {
        throw new AuthorizationNotProvidedException();
      }
      $token = str_replace('Bearer ', '', $headers['Authorization']);
      $user = JWT::decode($token, new Key(getenv('APP_JWT_SECRET'), 'HS256'));
      if (!isset($user->session_id)) {
        throw new UserNotAuthorizedException();
      }
      $fetchedUser = self::fetchRow(['id' => $user->id]);
      if ($fetchedUser) {
        self::$user = $fetchedUser;
        return self::$user;
      }
      throw new UserNotFoundException();
    }
    return self::$user;
  }
}
