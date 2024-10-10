<?php

namespace Ilias\Choir\Exceptions;

class AuthorizationNotProvidedException extends \Exception
{
  public function __construct()
  {
    parent::__construct('Authorization not provided');
  }
}
