<?php

namespace Ilias\Choir\Middleware;

use Ilias\Dotenv\Helper;
use Ilias\Rhetoric\Exceptions\MiddlewareException;
use Ilias\Rhetoric\Middleware\IMiddleware;

class EnvironmentMiddleware implements IMiddleware
{
  public static function handle()
  {
    if (Helper::env("ENVIRONMENT") !== "test") {
      throw new MiddlewareException("Cannot access this route out of the test environment.");
    }
  }
}
