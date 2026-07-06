<?php

declare(strict_types=1);

namespace App\Models;

final class ChinoisVocabulaire
{
    public int $id = 0;

    public string $langue = '';

    public string $mot = '';

    public string $pinyin = '';

    public string $type = '';

    public string $traduction = '';

    public string $exemple = '';

    public bool $maitrise = false;

    public bool $xp_rewarded = false;

    public int $position = 0;

    public string $created_at = '';
}
