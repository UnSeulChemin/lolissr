<?php

declare(strict_types=1);

namespace App\Repositories\Artbook;

use App\DTO\Artbook\Responses\ArtbookRepresentationData;
use App\DTO\Artbook\Responses\ArtbookStatsData;
use App\Repositories\AbstractRepository;

final class ArtbookStatsRepository extends AbstractRepository
{
    public function countRead(): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM {$this->readTable()} WHERE lu = 1");
        return (int) ($row->total ?? 0);
    }
    /** @return array{total: int, authors: int, series: int} */
    public function dashboardSummary(): array
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total,
            COUNT(DISTINCT CASE WHEN auteur <> '' THEN auteur END) AS authors,
            COUNT(DISTINCT CASE WHEN serie <> '' THEN serie END) AS series FROM {$this->readTable()}");
        return ['total' => (int) ($row->total ?? 0), 'authors' => (int) ($row->authors ?? 0),
            'series' => (int) ($row->series ?? 0)];
    }

    protected string $table = 'artbook';

    public function countAll(): int
    {
        return $this->countRows();
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

            FROM {$this->readTable()}

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
        $winner = $this->fetchOne(
            "SELECT * FROM (
                SELECT 'author' AS type, auteur AS name, COUNT(*) AS total,
                    MIN(thumbnail) AS thumbnail, MIN(extension) AS extension
                FROM {$this->readTable()}
                WHERE auteur IS NOT NULL AND auteur <> ''
                GROUP BY auteur
                UNION ALL
                SELECT 'series' AS type, serie AS name, COUNT(*) AS total,
                    MIN(thumbnail) AS thumbnail, MIN(extension) AS extension
                FROM {$this->readTable()}
                WHERE serie IS NOT NULL AND serie <> ''
                GROUP BY serie
            ) represented
            ORDER BY total DESC, CASE WHEN type = 'author' THEN 0 ELSE 1 END, name ASC
            LIMIT 1"
        );
        return $winner !== null
            ? $this->mapToRepresentationDto($winner)
            : null;
    }

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

    private function mapToStatsDto(object $row): ArtbookStatsData
    {
        /** @var array{
         *     artbook:string,
         *     auteur:?string,
         *     thumbnail:?string,
         *     extension:?string
         * } $data
         */
        $data = (array) $row;

        $thumbnailUrl = $this->buildThumbnailUrl($data['thumbnail'], $data['extension']);

        return new ArtbookStatsData(
            artbook: $data['artbook'],

            thumbnailUrl: $thumbnailUrl,

            authorLabel:
                $data['auteur']
                ?? 'Auteur inconnu'
        );
    }

    private function mapToRepresentationDto(object $row): ArtbookRepresentationData
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

        $thumbnailUrl = $this->buildThumbnailUrl($data['thumbnail'], $data['extension']);

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
                . ' artbooks'
        );
    }

    private function buildThumbnailUrl(?string $thumbnail, ?string $extension): string
    {
        if ($thumbnail === null || $extension === null)
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
