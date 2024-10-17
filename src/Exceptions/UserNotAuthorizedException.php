<?php

namespace Ilias\Choir\Exceptions;

use Ilias\Rhetoric\Exceptions\MiddlewareException;

class UserNotAuthorizedException extends MiddlewareException
{
  public function __construct()
  {
    parent::__construct('User not authorized');
  }
}
