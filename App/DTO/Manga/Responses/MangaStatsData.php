<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaStatsData
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $livre,

        public string $thumbnailUrl,
        public string $url,

        public int $numero,
        public string $numeroLabel,

        public ?int $total,
        public string $totalLabel,
    ) {
    }

    /**
     * @return array{
     *     id: int,
     *     slug: string,
     *     livre: string,
     *     thumbnailUrl: string,
     *     url: string,
     *     numero: int,
     *     numeroLabel: string,
     *     total: int|null,
     *     totalLabel: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'livre' => $this->livre,

            'thumbnailUrl' => $this->thumbnailUrl,
            'url' => $this->url,

            'numero' => $this->numero,
            'numeroLabel' => $this->numeroLabel,

            'total' => $this->total,
            'totalLabel' => $this->totalLabel,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            slug: (string) $data['slug'],
            livre: (string) $data['livre'],

            thumbnailUrl: (string) $data['thumbnailUrl'],
            url: (string) $data['url'],

            numero: (int) $data['numero'],
            numeroLabel: (string) $data['numeroLabel'],

            total: isset($data['total'])
                ? (int) $data['total']
                : null,

            totalLabel: (string) $data['totalLabel'],
        );
    }
}