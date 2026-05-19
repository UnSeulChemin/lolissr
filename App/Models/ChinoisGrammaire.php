<?php

declare(strict_types=1);

namespace App\Models;

final class ChinoisGrammaire
{
    public int $id;

    public string $niveau;

    public string $titre;

    public string $structure;

    public string $phrase;

    public string $pinyin;

    public string $traduction;

    public string $explication;

    public int $maitrise;

    public string $section;

    public int $section_position;

    public string $categorie;

    public int $categorie_position;

    public int $position;

    public ?string $created_at = null;
}