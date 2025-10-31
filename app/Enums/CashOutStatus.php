<?php

namespace App\Enums;

enum CashOutStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
