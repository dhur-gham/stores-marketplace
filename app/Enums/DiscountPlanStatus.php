<?php

namespace App\Enums;

enum DiscountPlanStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Expired = 'expired';
}
