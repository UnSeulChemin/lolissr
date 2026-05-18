<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

final class MangaUpdateNoteRequest
{
    public function __construct(
        private readonly array $data
    ) {}

    public static function from(array $data): self
    {
        return new self($data);
    }

    public function canonicalSlug(): string
    {
        return (string) ($this->data['canonicalSlug'] ?? '');
    }

    public function numero(): int
    {
        return (int) ($this->data['numero'] ?? 0);
    }

    public function note(): string
    {
        return (string) ($this->data['note'] ?? '');
    }
}