<?php

namespace Ilias\Choir\Model;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Maestro\Types\Unique;

final class User extends Table
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  public string $firstName;
  /** @not_nuable */
  public string $lastName;
  /** @unique */
  public string $nickname;
  /** @unique */
  public string $email;
  public string $password;
  public bool $active = true;
  public Timestamp | Expression | string $createdIn = Expression::CURRENT_TIMESTAMP;
  public Timestamp $updatedIn;
  public Timestamp $inactivatedIn;

  public function __construct(
    string $nickname,
    string $email,
    string $password,
    bool $active,
    Timestamp $createdIn
  ) {
    $this->nickname = $nickname;
    $this->email = $email;
    $this->password = $password;
    $this->active = $active;
    $this->createdIn = $createdIn;
  }
}
