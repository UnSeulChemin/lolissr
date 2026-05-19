<?php

declare(strict_types=1);

namespace App\Models;

final class ChinoisVocabulaire
{
    public int $id;

    public string $langue;

    public string $mot;

    public string $pinyin;

    public string $type;

    public string $traduction;

    public string $exemple;

    public string $created_at;
}