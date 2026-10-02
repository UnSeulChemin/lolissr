<?php

declare(strict_types=1);

namespace App\Repositories\Chinois\Concerns;

use stdClass;

trait ReadsFlashcardPage
{
    /** @return array{rows: list<stdClass>, total: int, offset: int} */
    private function readFlashcardCursor(int $id, bool $previous): array
    {
        $id = max(0, $id);
        $operator = $previous ? '<' : '>';
        $order = $previous ? 'DESC' : 'ASC';
        $fields = implode(', ', array_map(
            static fn (string $field): string => 'card.' . trim($field),
            explode(',', self::SELECT_FIELDS)
        ));
        // Seek and wrap using IDs. Only 50 IDs are materialized, with no window
        // ranking. Counts and contents still share one statement snapshot.
        $rows = $this->fetchAll("WITH candidates AS (
                SELECT id FROM {$this->table()} WHERE maitrise = 0 AND id {$operator} {$id}
                ORDER BY id {$order} LIMIT 50
            ), wrapped AS (
                SELECT id FROM {$this->table()} WHERE maitrise = 0
                    AND NOT EXISTS (SELECT 1 FROM candidates)
                ORDER BY id {$order} LIMIT 50
            ), selected AS (
                SELECT id FROM candidates UNION ALL SELECT id FROM wrapped
            ), totals AS (
                SELECT COUNT(*) AS total,
                    COUNT(CASE WHEN id < (SELECT MIN(id) FROM selected) THEN 1 END) AS page_offset
                FROM {$this->table()} WHERE maitrise = 0
            )
            SELECT {$fields}, totals.total AS flashcard_total, totals.page_offset
            FROM totals LEFT JOIN selected ON 1 = 1
            LEFT JOIN {$this->table()} card ON card.id = selected.id ORDER BY card.id");
        return [
            'total' => (int) ($rows[0]->flashcard_total ?? 0),
            'offset' => (int) ($rows[0]->page_offset ?? 0),
            'rows' => array_values(array_filter($rows, static fn (stdClass $row): bool => $row->id !== null)),
        ];
    }

    /** @return array{rows: list<stdClass>, total: int, offset: int} */
    private function readFlashcardPage(int $offset): array
    {
        $offset = max(0, $offset);
        $fields = implode(', ', array_map(
            static fn (string $field): string => 'card.' . trim($field),
            explode(',', self::SELECT_FIELDS)
        ));
        // Rank IDs only; load the card contents for at most 50 rows. Count,
        // clamped offset and contents all come from the same statement snapshot.
        $rows = $this->fetchAll("WITH ranked AS (
                SELECT id, ROW_NUMBER() OVER (ORDER BY id) AS position
                FROM {$this->table()} WHERE maitrise = 0
            ), totals AS (
                SELECT COUNT(*) AS total FROM ranked
            ), clamped AS (
                SELECT total, CASE
                    WHEN total = 0 THEN 0
                    WHEN {$offset} >= total THEN total - 1
                    ELSE {$offset} END AS requested_offset
                FROM totals
            ), bounds AS (
                SELECT total, requested_offset - (requested_offset % 50) AS page_offset
                FROM clamped
            )
            SELECT {$fields}, bounds.total AS flashcard_total, bounds.page_offset
            FROM bounds
            LEFT JOIN ranked ON ranked.position > bounds.page_offset
                AND ranked.position <= bounds.page_offset + 50
            LEFT JOIN {$this->table()} card ON card.id = ranked.id
            ORDER BY ranked.position");

        return [
            'total' => (int) ($rows[0]->flashcard_total ?? 0),
            'offset' => (int) ($rows[0]->page_offset ?? 0),
            'rows' => array_values(array_filter($rows, static fn (stdClass $row): bool => $row->id !== null)),
        ];
    }
}
