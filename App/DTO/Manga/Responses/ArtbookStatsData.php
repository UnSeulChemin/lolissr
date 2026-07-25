<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookStatsData
{
    public function __construct(
        public string $artbook,

        public string $thumbnailUrl,

        public string $authorLabel,
    ) {
    }

    /**
     * @return array{
     *     artbook: string,
     *     thumbnailUrl: string,
     *     authorLabel: string
     * }
     */
    public function toArray(): array
    {
        return [
            'artbook' => $this->artbook,
            'thumbnailUrl' => $this->thumbnailUrl,
            'authorLabel' => $this->authorLabel,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            artbook: (string) $data['artbook'],
            thumbnailUrl: (string) $data['thumbnailUrl'],
            authorLabel: (string) $data['authorLabel'],
        );
    }
}