<?php

namespace Ilias\Choir\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ilias\Opherator\Request;
use Ilias\Rhetoric\Exceptions\MiddlewareException;
use Ilias\Rhetoric\Middleware\IMiddleware;

class JwtMiddleware implements IMiddleware
{
  public static function handle()
  {
    $headers = Request::getHeaders();
    if (empty($headers['Authorization'])) {
      throw new MiddlewareException('Authorization header is required');
    }
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    try {
      JWT::decode($token, new Key(getenv('APP_JWT_SECRET'), 'HS256'));
    } catch (\Throwable $th) {
      throw new MiddlewareException('Invalid provided token');
    }
  }
}
