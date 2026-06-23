<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Home\Responses\LatestArtbookData;
use App\DTO\Home\Responses\MostRepresentedArtbookData;
use App\Models\Artbook;
use App\Models\Model;

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
                'slug' => $slug,
                'numero' => $numero,
            ],
            Artbook::class
        );

        return $artbook;
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
