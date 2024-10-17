<?php

namespace Ilias\Choir\Exceptions;

use Ilias\Rhetoric\Exceptions\MiddlewareException;

class UserNotFoundException extends MiddlewareException
{
  public function __construct()
  {
    parent::__construct('User not found');
  }
}
