<?php

namespace Ilias\Choir\Exceptions;

use Ilias\Rhetoric\Exceptions\MiddlewareException;

class AuthorizationNotProvidedException extends MiddlewareException
{
  public function __construct()
  {
    parent::__construct('Authorization not provided');
  }
}
