<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Artbook;
use App\Models\Model;

final class ArtbookRepository extends Model
{
    protected string $table =
        'artbook';

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

    public function findLatest(): ?Artbook
    {
        /** @var Artbook|null $artbook */
        $artbook =
            $this->fetchOne(
                "
                SELECT *
                FROM {$this->table()}
                ORDER BY created_at DESC
                LIMIT 1
                ",
                [],
                Artbook::class,
            );

        return $artbook;
    }
}
