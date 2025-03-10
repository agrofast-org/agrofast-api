<?php

namespace App\Enums;

enum UserError: string
{
    case INVALID_TOKEN = 'token_invalid';

    case MISSING_TOKEN = 'missing_token';

    case USER_NOT_FOUND = 'user_not_found';

    case SESSION_NOT_FOUND = 'session_not_found';

    case NO_SESSION = 'no_session';
}
