<?php

namespace App\Enums;

enum TaskStatusEnum: int
{
    case PENDING = 1;
    case COMPLETED = 2;
    case CANCELLED = 3;
}
