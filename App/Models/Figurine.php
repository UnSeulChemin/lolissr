<?php

declare(strict_types=1);

namespace App\Models;

final class Figurine
{
    public int $id = 0;

    public string $thumbnail = '';

    public string $extension = '';

    public string $slug = '';

    public int $numero = 1;

    public string $waifu = '';

    public string $scale = '';

    public ?float $height_cm = null;

    public string $company = '';

    public ?string $release_date = null;

    public ?string $commentaire = null;

    public string $created_at = '';
}