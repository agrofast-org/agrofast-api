<?php

namespace App\Enums;

enum UserProfileType: string
{
    case REQUESTER = 'requester';
    case TRANSPORTER = 'transporter';
}
