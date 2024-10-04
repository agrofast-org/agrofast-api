<?php

namespace Ilias\Choir\Database;

use Ilias\Choir\Database\Schemas\Chat;
use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Maestro\Abstract\Database;
use Ilias\Maestro\Types\Postgres;

final class AgrofastDB extends Database
{
  public Hr $Hr;
  public Transport $Transport;
  public Chat $Chat;

  public function __construct()
  {
    self::declareFunction(
      'generate_four_digit_auth_code',
      Postgres::TEXT,
      'CREATE OR REPLACE FUNCTION generate_four_digit_auth_code() RETURNS TEXT AS $$ BEGIN RETURN CAST(FLOOR(1000 + RANDOM() * 9000) AS TEXT); END; $$ LANGUAGE plpgsql;'
    );
  }
}
