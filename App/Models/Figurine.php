<?php

declare(strict_types=1);

namespace App\Models;

final class Figurine
{
    public int $id = 0;

    public string $thumbnail = '';

    public string $extension = '';

    public string $slug = '';

    public string $waifu = '';

    public string $company = '';

    public ?string $commentaire = null;

    public string $created_at = '';
}