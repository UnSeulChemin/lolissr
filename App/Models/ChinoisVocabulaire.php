<?php

declare(strict_types=1);

namespace App\Models;

final class ChinoisVocabulaire
{
    public int $id = 0;

    public string $langue = '';

    public string $mot = '';

    public ?string $pinyin = null;

    public string $type = '';

    public string $traduction = '';

    public ?string $exemple = null;

    public string $created_at = '';
}