<?php

declare(strict_types=1);

namespace App\DTO\Nendoroid\Inputs;

use Framework\Support\Str;

final readonly class NendoroidCreateDTO
{
    public function __construct(
        public string $waifu,
        public int $numero,
        public string $company,
        public string $slug,
        public ?string $commentaire
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $waifu = trim((string) ($data['waifu'] ?? ''));

        return new self(
            waifu: $waifu,
            numero: max(1, (int) ($data['numero'] ?? 1)),
            company: trim((string) ($data['company'] ?? '')),
            slug: Str::slug((string) ($data['slug'] ?? $waifu)),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }
}