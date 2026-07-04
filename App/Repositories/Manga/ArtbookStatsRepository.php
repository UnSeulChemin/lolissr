<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Home\Responses\LatestArtbookData;
use App\DTO\Home\Responses\MostRepresentedArtbookData;
use App\Models\Model;

final class ArtbookStatsRepository extends Model
{
    protected string $table = 'artbook';

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

    public function countAll(): int
    {
        return $this->countRows();
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

        return $winner !== null
            ? $this->mapMostRepresented($winner)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapLatestArtbook(object $row): LatestArtbookData
    {
        /** @var array{
         *     artbook:string,
         *     auteur:?string,
         *     thumbnail:?string,
         *     extension:?string
         * } $data
         */
        $data = (array) $row;

        $thumbnailUrl = '';

        if (
            $data['thumbnail'] !== null
            && $data['extension'] !== null
        ) {
            $thumbnailUrl =
                '/images/artbook/thumbnail/'
                . $data['thumbnail']
                . '.'
                . $data['extension'];
        }

        return new LatestArtbookData(
            artbook: $data['artbook'],
            auteur: $data['auteur'],
            thumbnailUrl: $thumbnailUrl,
            authorLabel: $data['auteur'] ?? 'Auteur inconnu',
        );
    }

    private function mapMostRepresented(object $row): MostRepresentedArtbookData
    {
        /** @var array{
         *     type:string,
         *     name:string,
         *     total:int|string,
         *     thumbnail:?string,
         *     extension:?string
         * } $data
         */
        $data = (array) $row;

        $thumbnailUrl = '';

        if (
            $data['thumbnail'] !== null
            && $data['extension'] !== null
        ) {
            $thumbnailUrl =
                '/images/artbook/thumbnail/'
                . $data['thumbnail']
                . '.'
                . $data['extension'];
        }

        return new MostRepresentedArtbookData(
            name: $data['name'],
            total: (int) $data['total'],

            title:
                $data['type'] === 'author'
                    ? '🎨 Auteur le plus représenté'
                    : '📚 Série la plus représentée',

            countLabel:
                (int) $data['total'] . ' artbooks',

            thumbnailUrl: $thumbnailUrl,
        );
    }

    /**
     * @param array<string,mixed> $params
     */
    private function fetchSingleValue(
        string $sql,
        string $field,
        array $params = [],
        mixed $default = 0,
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