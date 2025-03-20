<?php

namespace App\Enums;

enum UserAction: string
{
    case AUTHENTICATE = 'authenticate';
    case AUTHENTICATED = 'authenticated';
}
