<?php

namespace App\Enums;
use App\Traits\Enumerable;

enum CommentStatuses: string
{
    use Enumerable;
    case Approved = 'تایید شده';
    case Pending = 'در انتظار تایید';
    case Spam = 'اسپم';
} 