<?php

declare(strict_types=1);

namespace App\DTO\Manga\Results;

final readonly class UpdateNoteResultData
{
    public function __construct(
        public int $jacquette,
        public int $livreNote,
        public int $note,
    ) {
    }
}