<?php

namespace Ilias\Choir\Database\Schemas;

use Ilias\Choir\Model\Chat\Message;
use Ilias\Maestro\Abstract\Schema;

final class Chat extends Schema
{
  public Message $Message;
}
