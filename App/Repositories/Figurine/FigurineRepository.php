<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Models\Figurine;
use App\Models\Model;
use App\DTO\Figurine\Inputs\FigurineUpdateDTO;

use Framework\Support\Str;

final class FigurineRepository extends Model
{
    protected string $table = 'figurine';

    /**
     * @return list<Figurine>
     */
    public function findAll(): array
    {
        /** @var list<Figurine> $figurines */
        $figurines = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            ORDER BY waifu ASC
            ",
            [],
            Figurine::class
        );

        return $figurines;
    }

    /**
     * @return list<Figurine>
     */
    public function findPaginated(
        int $limit,
        int $page,
    ): array
    {
        $offset = ($page - 1) * $limit;

        /** @var list<Figurine> $figurines */
        $figurines = $this->fetchAll(
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
            Figurine::class
        );

        return $figurines;
    }

    public function findBySlug(string $slug): ?Figurine
    {
        /** @var Figurine|null $figurine */
        $figurine = $this->fetchOne(
            "
            SELECT *

            FROM {$this->table()}

            WHERE slug = :slug

            LIMIT 1
            ",
            [
                'slug' => $this->normalizeSlug($slug),
            ],
            Figurine::class
        );

        return $figurine;
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
        return parent::insert($this->normalizeInsertData($data));
    }

    public function updateFigurine(
        string $slug,
        FigurineUpdateDTO $dto
    ): bool
    {
        return $this->updateBySlug(
            $slug,
            [
                'company' => $dto->company,
                'commentaire' => $dto->commentaire,
            ]
        );
    }

    public function deleteBySlug(string $slug): bool
    {
        return $this->delete([
            'slug' => $this->normalizeSlug($slug),
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
     * @param array<string, mixed> $data
     */
    private function updateBySlug(
        string $slug,
        array $data
    ): bool
    {
        return $this->update(
            $data,
            [
                'slug' => $this->normalizeSlug($slug),
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
            'waifu' => trim((string) ($data['waifu'] ?? '')),
            'company' => trim((string) ($data['company'] ?? '')),
            'commentaire' => Str::nullableTrim($data['commentaire'] ?? null),
        ];
    }
}