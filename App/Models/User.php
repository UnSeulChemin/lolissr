<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    public int $id;

    public string $username;

    public string $password;

    public int $level;

    public int $xp;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}