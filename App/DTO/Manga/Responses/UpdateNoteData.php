<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class UpdateNoteData
{
    public function __construct(
        public int $jacquette,
        public int $livreNote,
        public int $note
    ) {
    }
}
