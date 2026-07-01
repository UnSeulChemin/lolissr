<?php

declare(strict_types=1);

namespace App\Repositories\Manga\Concerns;

trait HasMangaStatsSubQuery
{
    abstract protected function table(): string;

    private function statsSubQuery(): string
    {
        return "
            SELECT
                slug,
                COUNT(*) AS total,
                SUM(
                    CASE
                        WHEN lu = 1 THEN 1
                        ELSE 0
                    END
                ) AS total_lu,
                ROUND(
                    AVG(
                        COALESCE(note, 0)
                    ),
                    1
                ) AS average_note

            FROM {$this->table()}

            GROUP BY slug
        ";
    }
}