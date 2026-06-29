<?php

declare(strict_types=1);

namespace App\DTO\Peluche\Inputs;

use Framework\Support\Str;

final readonly class PelucheUpdateDTO
{
    public function __construct(
        public ?string $company,
        public ?string $commentaire
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            company: Str::nullableTrim($data['company'] ?? null),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }
}