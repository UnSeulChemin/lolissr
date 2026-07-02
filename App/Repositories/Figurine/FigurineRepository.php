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
        /** @var list<Figurine> $figurine */
        $figurines = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            ORDER BY waifu ASC, numero ASC
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

            ORDER BY waifu ASC, numero ASC

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

    public function findOneBySlugAndNumero(
        string $slug,
        int $numero
    ): ?Figurine
    {
        /** @var Figurine|null $figurine */
        $figurine = $this->fetchOne(
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
        int $numero,
        FigurineUpdateDTO $dto
    ): bool
    {
        return $this->updateBySlugAndNumero(
            $slug,
            $numero,
            [
                'waifu' => $dto->waifu,
                'scale' => $dto->scale,
                'height_cm' => $dto->height_cm,
                'company' => $dto->company,
                'release_date' => $dto->release_date,
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
     * @param array<string, mixed> $data
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
            'scale' => trim((string) ($data['scale'] ?? '')),
            'height_cm' => isset($data['height_cm']) && $data['height_cm'] !== ''
                ? (float) $data['height_cm']
                : null,
            'company' => trim((string) ($data['company'] ?? '')),
            'release_date' => Str::nullableTrim($data['release_date'] ?? null),

            'commentaire' => Str::nullableTrim($data['commentaire'] ?? null),
        ];
    }
}