<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\Models\Peluche;
use App\Models\Model;
use App\DTO\Peluche\Inputs\PelucheUpdateDTO;

use Framework\Support\Str;

final class PelucheRepository extends Model
{
    protected string $table = 'peluche';

    /**
     * @return list<Peluche>
     */
    public function findAll(): array
    {
        /** @var list<Peluche> $peluches */
        $peluches = $this->fetchAll(
            "
            SELECT p.*

            FROM {$this->table()} p

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = p.slug

            ORDER BY
                grouped.last_id DESC,
                p.numero DESC
            ",
            [],
            Peluche::class
        );

        return $peluches;
    }

    /**
     * @return list<Peluche>
     */
    public function findPaginated(
        int $limit,
        int $page,
    ): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        /** @var list<Peluche> $peluches */
        $peluches = $this->fetchAll(
            "
            SELECT p.*

            FROM {$this->table()} p

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = p.slug

            ORDER BY
                grouped.last_id DESC,
                p.numero DESC

            LIMIT :limit
            OFFSET :offset
            ",
            [
                'limit' => $limit,
                'offset' => $offset,
            ],
            Peluche::class
        );

        return $peluches;
    }

    public function findOneBySlugAndNumero(
        string $slug,
        int $numero
    ): ?Peluche
    {
        /** @var Peluche|null $peluche */
        $peluche = $this->fetchOne(
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
            Peluche::class
        );

        return $peluche;
    }

    public function countAll(): int
    {
        return $this->countRows();
    }

    /**
     * @param array<string,mixed> $data
     */
    public function insert(array $data): bool
    {
        return parent::insert(
            $this->normalizeInsertData($data)
        );
    }

    public function updatePeluche(
        string $slug,
        int $numero,
        PelucheUpdateDTO $dto
    ): bool
    {
        return $this->updateBySlugAndNumero(
            $slug,
            $numero,
            [
                'company' => $dto->company,
                'commentaire' => $dto->commentaire,
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

    private function normalizeSlug(string $slug): string
    {
        return Str::slug($slug);
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
            'waifu' => trim((string) ($data['waifu'] ?? '')),
            'company' => trim((string) ($data['company'] ?? '')),
            'commentaire' => Str::nullableTrim($data['commentaire'] ?? null),
        ];
    }
}