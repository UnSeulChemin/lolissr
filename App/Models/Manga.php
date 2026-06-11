<?php

declare(strict_types=1);

namespace App\Models;

final class Manga
{
    public int $id = 0;

    public string $thumbnail = '';

    public string $extension = '';

    public string $slug = '';

    public string $livre = '';

    public ?string $editeur = null;

    public int $numero = 0;

    public int $lu = 0;

    public int $xp_read_rewarded = 0;

    public string $statut = 'en_cours';

    public ?int $jacquette = null;

    public ?int $livre_note = null;

    public ?int $note = null;

    public ?string $commentaire = null;

    public string $created_at = '';

    public ?int $total = null;

    public ?int $total_lu = null;

    public ?float $average_note = null;
}
