<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Inputs;

use Framework\Support\Str;

use DateTime;

final readonly class FigurineUpdateDTO
{
    public function __construct(
        public ?string $waifu,
        public ?string $origin,
        public ?string $scale,
        public ?float $height_cm,
        public ?string $company,
        public ?string $release_date,
        public ?string $commentaire
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            waifu: Str::nullableTrim($data['waifu'] ?? null),
            origin: Str::nullableTrim($data['origin'] ?? null),
            scale: Str::nullableTrim($data['scale'] ?? null),
            height_cm: isset($data['height_cm']) && $data['height_cm'] !== ''
                ? (float) $data['height_cm']
                : null,
            company: Str::nullableTrim($data['company'] ?? null),
            release_date: self::normalizeDate(
                Str::nullableTrim($data['release_date'] ?? null),
            ),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }

    private static function normalizeDate(
        ?string $date,
    ): ?string
    {
        if ($date === null)
        {
            return null;
        }

        $parsed = DateTime::createFromFormat(
            'd/m/Y',
            $date,
        );

        return $parsed !== false
            ? $parsed->format('Y-m-d')
            : $date;
    }
}