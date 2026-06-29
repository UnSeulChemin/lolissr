<?php

declare(strict_types=1);

namespace App\Repositories\Nendoroid;

use App\DTO\Nendoroid\Inputs\NendoroidUpdateDTO;
use App\Models\Model;
use App\Models\Nendoroid;

use Framework\Support\Str;

final class NendoroidRepository extends Model
{
    protected string $table = 'nendoroid';

    /**
     * @return list<Nendoroid>
     */
    public function findAll(): array
    {
        /** @var list<Nendoroid> $nendoroids */
        $nendoroids = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            ORDER BY waifu ASC
            ",
            [],
            Nendoroid::class
        );

        return $nendoroids;
    }

    /**
     * @return list<Nendoroid>
     */
    public function findPaginated(
        int $limit,
        int $page,
    ): array
    {
        $offset = ($page - 1) * $limit;

        /** @var list<Nendoroid> $nendoroids */
        $nendoroids = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            ORDER BY waifu ASC

            LIMIT :limit
            OFFSET :offset
            ",
            [
                'limit' => $limit,
                'offset' => $offset,
            ],
            Nendoroid::class
        );

        return $nendoroids;
    }

    public function findOneBySlugAndNumero(
        string $slug,
        int $numero
    ): ?Nendoroid
    {
        /** @var Nendoroid|null $nendoroid */
        $nendoroid = $this->fetchOne(
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
            Nendoroid::class
        );

        return $nendoroid;
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

    public function updateNendoroid(
        string $slug,
        int $numero,
        NendoroidUpdateDTO $dto
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