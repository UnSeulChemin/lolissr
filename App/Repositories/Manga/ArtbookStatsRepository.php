<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Manga\Responses\ArtbookStatsData;
use App\DTO\Manga\Responses\ArtbookRepresentationData;
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

    public function countRead(): int
    {
        return (int) $this->fetchSingleValue(
            "
            SELECT COUNT(*) AS total

            FROM {$this->table()}

            WHERE lu = 1
            ",
            'total',
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

    public function findLatest(): ?ArtbookStatsData
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

        return $row !== null
            ? $this->mapToStatsDto($row)
            : null;
    }

    public function findMostRepresented(): ?ArtbookRepresentationData
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
            ? $this->mapToRepresentationDto($winner)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapToStatsDto(
        object $row,
    ): ArtbookStatsData
    {
        /** @var array{
         *     artbook:string,
         *     auteur:?string,
         *     thumbnail:?string,
         *     extension:?string
         * } $data
         */
        $data = (array) $row;

        $thumbnailUrl = $this->buildThumbnailUrl(
            $data['thumbnail'],
            $data['extension'],
        );

        return new ArtbookStatsData(
            artbook: $data['artbook'],

            thumbnailUrl: $thumbnailUrl,

            authorLabel:
                $data['auteur']
                ?? 'Auteur inconnu',
        );
    }

    private function mapToRepresentationDto(
        object $row,
    ): ArtbookRepresentationData
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

        $thumbnailUrl = $this->buildThumbnailUrl(
            $data['thumbnail'],
            $data['extension'],
        );

        return new ArtbookRepresentationData(
            title:
                $data['type'] === 'author'
                    ? '📕 Auteur le plus représenté'
                    : '📚 Série la plus représentée',

            name: $data['name'],

            thumbnailUrl: $thumbnailUrl,

            total: (int) $data['total'],

            countLabel:
                (int) $data['total']
                . ' artbooks',
        );
    }

    private function buildThumbnailUrl(
        ?string $thumbnail,
        ?string $extension,
    ): string
    {
        if (
            $thumbnail === null
            || $extension === null
        )
        {
            return 'images/artbook/placeholder-artbook.webp';
        }

        return
            'images/artbook/thumbnail/'
            . $thumbnail
            . '.'
            . $extension;
    }
}
