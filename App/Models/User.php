<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    public int $id = 0;

    public string $avatar = 'default';

    public string $avatar_extension = 'png';

    public string $username = '';

    public string $password = '';

    public string $title = 'Explorateur';

    public int $level = 1;

    public int $xp = 0;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
