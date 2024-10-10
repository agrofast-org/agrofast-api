<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Database\Insert;
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

  public function compose(string $code)
  {
    $this->code = $code;
  }
}
