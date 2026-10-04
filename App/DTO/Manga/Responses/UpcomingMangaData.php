<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class UpcomingMangaData
{
    public function __construct(
        public int $number,
        public string $date,
        public string $dateLabel,
        public string $sourceUrl,
        public ?string $imageUrl
    )
    {}
}
