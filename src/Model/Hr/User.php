<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Maestro\Types\Unique;

final class User extends TrackableTable
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @not_nuable */
  public string $name;
  /** @unique */
  public string $number;
  public string $password;
  public bool $authenticated = false;

  public function __construct(
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
}
