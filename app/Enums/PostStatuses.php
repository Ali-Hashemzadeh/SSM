<?php

namespace App\Enums;
use App\Traits\Enumerable;

enum PostStatuses: string
{
    use Enumerable;
    case Pending = 'در انتظار تایید';
    case Approved = 'تایید شده';
    case Rejected = 'رد شده';
    case NeedsCorrection = 'نیازمند اصلاح';
} 