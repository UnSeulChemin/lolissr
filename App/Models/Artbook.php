<?php

declare(strict_types=1);

namespace App\Models;

final class Artbook
{
    public int $id = 0;

    public string $thumbnail = '';

    public string $extension = '';

    public string $slug = '';

    public int $numero = 1;

    public string $artbook = '';

    public ?string $auteur = null;

    public ?string $serie = null;

    public string $created_at = '';
}