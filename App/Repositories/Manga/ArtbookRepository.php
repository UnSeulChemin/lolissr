<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Artbook;
use App\Models\Model;
use App\DTO\Home\Responses\MostRepresentedArtbookData;
use App\DTO\Home\Responses\LatestArtbookData;

final class ArtbookRepository extends Model
{
    protected string $table =
        'artbook';

    private function mapLatestArtbook(
        object $row,
    ): LatestArtbookData {
        return new LatestArtbookData(
            artbook: (string) $row->artbook,
            auteur: $row->auteur,
            thumbnail: $row->thumbnail,
            extension: $row->extension,
        );
    }

    private function mapMostRepresented(
        object $row,
    ): MostRepresentedArtbookData {
        return new MostRepresentedArtbookData(
            type: (string) $row->type,
            name: (string) $row->name,
            total: (int) $row->total,
            thumbnail: $row->thumbnail,
            extension: $row->extension,
        );
    }

    /**
     * @return list<Artbook>
     */
    public function findAll(): array
    {
        /** @var list<Artbook> $artbooks */
        $artbooks =
            $this->fetchAll(
                "
                SELECT *
                FROM {$this->table()}
                ORDER BY created_at DESC
                ",
                [],
                Artbook::class,
            );

        return $artbooks;
    }

    public function countAll(): int
    {
        return $this->countRows();
    }

    public function countAuthors(): int
    {
        $result =
            $this->fetchOne(
                "
                SELECT
                    COUNT(
                        DISTINCT auteur
                    ) AS total
                FROM {$this->table()}
                WHERE auteur IS NOT NULL
                AND auteur <> ''
                ",
            );

        /** @var array{total?: mixed} $data */
        $data =
            (array) $result;

        return (int) (
            $data['total']
            ?? 0
        );
    }

    public function countSeries(): int
    {
        $result =
            $this->fetchOne(
                "
                SELECT
                    COUNT(
                        DISTINCT serie
                    ) AS total
                FROM {$this->table()}
                WHERE serie IS NOT NULL
                AND serie <> ''
                ",
            );

        /** @var array{total?: mixed} $data */
        $data =
            (array) $result;

        return (int) (
            $data['total']
            ?? 0
        );
    }

    public function findLatest(): ?LatestArtbookData
    {
        $row =
            $this->fetchOne(
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
            ? $this->mapLatestArtbook($row)
            : null;
    }

    public function findMostRepresented(): ?MostRepresentedArtbookData
    {
        $author =
            $this->fetchOne(
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

        $series =
            $this->fetchOne(
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

        $authorTotal =
            (int) (($author?->total) ?? 0);

        $seriesTotal =
            (int) (($series?->total) ?? 0);

        if (
            $authorTotal === 0
            && $seriesTotal === 0
        ) {
            return null;
        }

        $winner =
            $authorTotal >= $seriesTotal
                ? $author
                : $series;

        return $winner !== null
            ? $this->mapMostRepresented($winner)
            : null;
    }
}
