<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use App\Models\User\User;

final readonly class LoginIdentityData
{
    public function __construct(public string $username, public ?User $user)
    {
    }
}
