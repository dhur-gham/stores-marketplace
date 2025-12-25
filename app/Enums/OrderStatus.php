<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Processing = 'processing';
    case Dispatched = 'dispatched';
    case Complete = 'complete';
    case Cancelled = 'cancelled';
}
