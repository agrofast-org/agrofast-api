<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Choir\Service\SmsSender;
use Ilias\Choir\Utilities\Utils;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Types\Serial;

final class AuthCode extends TrackableTable
{
  public Hr $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $userId;
  public string|Expression $code = 'generate_four_digit_auth_code()';

  public function __construct($params) {
    parent::__construct(...$params);
  }

  public function compose(string $code)
  {
    $this->code = $code;
  }

  public static function create(int $userId): array
  {
    $user = User::fetchRow(['id' => $userId]);
    if ($user) {
      if (!empty(Utils::validatePhoneNumber($user->number))) {
        throw new \Exception('Invalid phone number');
      }
      $insert = new Insert();
      $insert->into(AuthCode::class)
        ->values(['user_id' => $user->id])
        ->returning(['*']);
      $authCode = $insert->execute()[0];
      
      // SmsSender::send($user->number, "Seu codigo de autenticacao para o Agrofast e: {$authCode['code']}");

      return $authCode;
    }
    throw new \Exception('User not found');
  }
}
