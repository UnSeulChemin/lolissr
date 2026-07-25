<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookRepresentationData
{
    public function __construct(
        public string $title,
        public string $name,

        public string $thumbnailUrl,

        public int $total,
        public string $countLabel,
    ) {
    }

    /**
     * @return array{
     *     title: string,
     *     name: string,
     *     thumbnailUrl: string,
     *     total: int,
     *     countLabel: string
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'name' => $this->name,

            'thumbnailUrl' => $this->thumbnailUrl,

            'total' => $this->total,
            'countLabel' => $this->countLabel,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) $data['title'],
            name: (string) $data['name'],

            thumbnailUrl: (string) $data['thumbnailUrl'],

            total: (int) $data['total'],
            countLabel: (string) $data['countLabel'],
        );
    }
}