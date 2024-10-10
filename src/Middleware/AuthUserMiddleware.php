<?php

namespace Ilias\Choir\Middleware;

use Ilias\Choir\Exceptions\AuthorizationNotProvidedException;
use Ilias\Choir\Exceptions\UserNotFoundException;
use Ilias\Choir\Model\Hr\User;
use Ilias\Rhetoric\Exceptions\MiddlewareException;

class AuthUserMiddleware
{
  public static function handle()
  {
    try {
      User::getAuthenticatedUser();
    } catch (AuthorizationNotProvidedException) {
      throw new MiddlewareException('Authorization header is required');
    } catch (UserNotFoundException) {
      throw new MiddlewareException('Invalid provided token');
    } catch (\Throwable $th) {
      throw new MiddlewareException('Could not authenticate user');
    }
  }
}
