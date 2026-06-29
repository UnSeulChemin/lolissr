<?php

declare(strict_types=1);

namespace App\Models;

final class Nendoroid
{
    public int $id = 0;

    public string $thumbnail = '';

    public string $extension = '';

    public string $slug = '';

    public int $numero = 1;

    public string $waifu = '';

    public string $company = '';

    public ?string $commentaire = null;

    public string $created_at = '';
}