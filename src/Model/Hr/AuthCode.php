<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;

final class AuthCode extends TrackableTable
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @not_nuable */
  public User $userId;
  public string|Expression $code = 'generate_four_digit_auth_code()';

  public function __construct(string $code)
  {
    $this->code = $code;
  }

  public static function createCode(int $userId, string $number)
  {
    $select = new Select(pdo: PDOConnection::get());
    $authCode = $select->from(['a' => AuthCode::class])
      ->where(['a.id' => $userId], Query::AND , compaction: Query::EQUALS)
      ->where(['a.created_at' => new Timestamp(strtotime('-2 hours'))], Query::AND , compaction: Query::LESS_THAN_OR_EQUAL)
      ->order('a.created_at', Select::DESC)
      ->execute()[0];
    if ($authCode) {
      return $authCode;
    }
    $insert = new Insert(pdo: PDOConnection::get());
    return $insert->into(User::class)->values(['user_id' => $userId])->returning(['id'])->execute()[0];
  }
}
