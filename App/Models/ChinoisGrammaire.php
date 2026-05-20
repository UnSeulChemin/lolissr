<?php

declare(strict_types=1);

namespace App\Models;

final class ChinoisGrammaire
{
    public ?int $id = null;

    public ?string $niveau = null;

    public ?string $titre = null;

    public ?string $structure = null;

    public ?string $phrase = null;

    public ?string $pinyin = null;

    public ?string $traduction = null;

    public ?string $explication = null;

    public ?int $maitrise = null;

    public ?string $section = null;

    public ?int $section_position = null;

    public ?string $categorie = null;

    public ?int $categorie_position = null;

    public ?int $position = null;

    public ?string $created_at = null;
}