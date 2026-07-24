<?php

declare(strict_types=1);

namespace App\Enums\Auth;

enum LoginResult
{
    case SUCCESS;
    case INVALID_CREDENTIALS;
    case LOCKED;
}