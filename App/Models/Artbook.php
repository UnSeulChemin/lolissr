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

    public bool $lu = false;

    public bool $xp_read_rewarded = false;

    public string $artbook = '';

    public ?string $auteur = null;

    public ?string $serie = null;

    public string $company = '';

    public ?string $release_date = null;

    public ?string $commentaire = null;

    public string $created_at = '';
}