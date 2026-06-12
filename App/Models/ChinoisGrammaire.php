<?php

declare(strict_types=1);

namespace App\Models;

final class ChinoisGrammaire
{
    public int $id = 0;

    public string $niveau = '';

    public string $titre = '';

    public string $structure = '';

    public ?string $abreviation = null;

    public string $phrase = '';

    public string $pinyin = '';

    public string $traduction = '';

    public string $explication = '';

    public bool $maitrise = false;

    public bool $xp_rewarded = false;

    public string $section = '';

    public int $section_position = 0;

    public string $categorie = '';

    public int $categorie_position = 0;

    public int $position = 0;

    public string $created_at = '';
}