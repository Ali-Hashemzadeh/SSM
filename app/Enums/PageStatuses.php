<?php

namespace App\Enums;

use App\Traits\Enumerable;

enum PageStatuses: string
{
    use Enumerable;
    case Pending = 'در انتظار تایید';
    case Approved = 'تایید شده';
    case Rejected = 'رد شده';
} 