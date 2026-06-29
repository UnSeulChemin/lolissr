<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Home\Responses\LatestArtbookData;
use App\DTO\Home\Responses\MostRepresentedArtbookData;
use App\Models\Artbook;
use App\Models\Model;
use App\DTO\Manga\Inputs\ArtbookUpdateDTO;

use Framework\Support\Str;

final class ArtbookRepository extends Model
{
    protected string $table = 'artbook';

    /**
     * @return list<Artbook>
     */
    public function findAll(): array
    {
        /** @var list<Artbook> $artbooks */
        $artbooks = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            ORDER BY created_at DESC
            ",
            [],
            Artbook::class
        );

        return $artbooks;
    }

    /**
     * @return list<Artbook>
     */
    public function findPaginated(
        int $limit,
        int $page,
    ): array
    {
        $offset = ($page - 1) * $limit;

        /** @var list<Artbook> $artbooks */
        $artbooks = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            ORDER BY created_at DESC

            LIMIT :limit
            OFFSET :offset
            ",
            [
                'limit' => $limit,
                'offset' => $offset,
            ],
            Artbook::class
        );

        return $artbooks;
    }

    public function countAll(): int
    {
        return $this->countRows();
    }

    public function countAuthors(): int
    {
        return (int) $this->fetchSingleValue(
            "
            SELECT COUNT(DISTINCT auteur) AS total

            FROM {$this->table()}

            WHERE auteur IS NOT NULL
            AND auteur <> ''
            ",
            'total'
        );
    }

    public function countSeries(): int
    {
        return (int) $this->fetchSingleValue(
            "
            SELECT COUNT(DISTINCT serie) AS total

            FROM {$this->table()}

            WHERE serie IS NOT NULL
            AND serie <> ''
            ",
            'total'
        );
    }

    public function findLatest(): ?LatestArtbookData
    {
        $row = $this->fetchOne(
            "
            SELECT
                artbook,
                auteur,
                thumbnail,
                extension

            FROM {$this->table()}

            ORDER BY created_at DESC

            LIMIT 1
            "
        );

        return $row !== null ? $this->mapLatestArtbook($row) : null;
    }

    public function findMostRepresented(): ?MostRepresentedArtbookData
    {
        $author = $this->fetchOne(
            "
            SELECT
                'author' AS type,
                auteur AS name,
                COUNT(*) AS total,
                MIN(thumbnail) AS thumbnail,
                MIN(extension) AS extension

            FROM {$this->table()}

            WHERE auteur IS NOT NULL
            AND auteur <> ''

            GROUP BY auteur

            ORDER BY total DESC

            LIMIT 1
            "
        );

        $series = $this->fetchOne(
            "
            SELECT
                'series' AS type,
                serie AS name,
                COUNT(*) AS total,
                MIN(thumbnail) AS thumbnail,
                MIN(extension) AS extension

            FROM {$this->table()}

            WHERE serie IS NOT NULL
            AND serie <> ''

            GROUP BY serie

            ORDER BY total DESC

            LIMIT 1
            "
        );

        $authorTotal = $author !== null ? (int) $author->total : 0;
        $seriesTotal = $series !== null ? (int) $series->total : 0;

        if ($authorTotal === 0 && $seriesTotal === 0)
        {
            return null;
        }

        $winner = $authorTotal >= $seriesTotal ? $author : $series;

        return $winner !== null ? $this->mapMostRepresented($winner) : null;
    }

    public function findOneBySlugAndNumero(string $slug, int $numero): ?Artbook
    {
        /** @var Artbook|null $artbook */
        $artbook = $this->fetchOne(
            "
            SELECT *

            FROM {$this->table()}

            WHERE slug = :slug
            AND numero = :numero

            LIMIT 1
            ",
            [
                'slug' => $this->normalizeSlug($slug),
                'numero' => $numero,
            ],
            Artbook::class
        );

        return $artbook;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): bool
    {
        return parent::insert(
            $this->normalizeInsertData($data)
        );
    }

    public function updateArtbook(
        string $slug,
        int $numero,
        ArtbookUpdateDTO $dto
    ): bool
    {
        return $this->updateBySlugAndNumero(
            $slug,
            $numero,
            [
                'artbook' => trim($dto->artbook),
                'auteur'  => Str::nullableTrim($dto->auteur),
                'serie'   => Str::nullableTrim($dto->serie),
            ]
        );
    }

    public function deleteBySlugAndNumero(
        string $slug,
        int $numero
    ): bool
    {
        return $this->delete([
            'slug' => $this->normalizeSlug($slug),
            'numero' => $numero,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapLatestArtbook(object $row): LatestArtbookData
    {
        /** @var array{
         *     artbook: string,
         *     auteur: ?string,
         *     thumbnail: ?string,
         *     extension: ?string
         * } $data
         */
        $data = (array) $row;

        return new LatestArtbookData(
            artbook: $data['artbook'],
            auteur: $data['auteur'],
            thumbnail: $data['thumbnail'],
            extension: $data['extension']
        );
    }

    private function mapMostRepresented(object $row): MostRepresentedArtbookData
    {
        /** @var array{
         *     type: string,
         *     name: string,
         *     total: int|string,
         *     thumbnail: ?string,
         *     extension: ?string
         * } $data
         */
        $data = (array) $row;

        return new MostRepresentedArtbookData(
            type: $data['type'],
            name: $data['name'],
            total: (int) $data['total'],
            thumbnail: $data['thumbnail'],
            extension: $data['extension']
        );
    }

    private function normalizeSlug(string $slug): string
    {
        return Str::slug($slug);
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function normalizeInsertData(array $data): array
    {
        return [
            'thumbnail' => trim((string) ($data['thumbnail'] ?? '')),
            'extension' => strtolower(trim((string) ($data['extension'] ?? ''))),
            'slug' => $this->normalizeSlug((string) ($data['slug'] ?? '')),
            'numero' => max(1, (int) ($data['numero'] ?? 1)),
            'artbook' => trim((string) ($data['artbook'] ?? '')),
            'auteur' => Str::nullableTrim($data['auteur'] ?? null),
            'serie' => Str::nullableTrim($data['serie'] ?? null),
        ];
    }

    /**
     * @param array<string,mixed> $data
     */
    private function updateBySlugAndNumero(
        string $slug,
        int $numero,
        array $data
    ): bool
    {
        return $this->update(
            $data,
            [
                'slug' => $this->normalizeSlug($slug),
                'numero' => $numero,
            ]
        );
    }

    /**
     * @param array<string, mixed> $params
     */
    private function fetchSingleValue(
        string $sql,
        string $field,
        array $params = [],
        mixed $default = 0
    ): mixed
    {
        $result = $this->fetchOne($sql, $params);

        if ($result === null)
        {
            return $default;
        }

        $resultArray = (array) $result;

        return $resultArray[$field] ?? $default;
    }
}
