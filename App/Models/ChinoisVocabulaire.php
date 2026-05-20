<?php

declare(strict_types=1);

namespace App\Models;

final class ChinoisVocabulaire
{
    public ?int $id = null;

    public ?string $langue = null;

    public ?string $mot = null;

    public ?string $pinyin = null;

    public ?string $type = null;

    public ?string $traduction = null;

    public ?string $exemple = null;

    public ?string $created_at = null;
}
