<?php

namespace Ilias\Choir\Database;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Maestro\Abstract\Database;

final class AgrofastDB extends Database
{
  public Hr $Hr;
  public Transport $Transport;
}
