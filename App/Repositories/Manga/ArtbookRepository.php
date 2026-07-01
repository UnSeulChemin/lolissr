<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Artbook;
use App\Models\Model;
use App\DTO\Manga\Inputs\ArtbookUpdateDTO;

use Framework\Support\Str;

final class ArtbookRepository extends Model
{
    protected string $table = 'artbook';

    public function findOneBySlugAndNumero(string $slug, int $numero): ?Artbook
    {
        /** @var Artbook|null $artbook */
        $artbook = $this->fetchOne(
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
            Artbook::class
        );

        return $artbook;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): bool
    {
        return parent::insert(
            $this->normalizeInsertData($data)
        );
    }

    public function updateArtbook(
        string $slug,
        int $numero,
        ArtbookUpdateDTO $dto
    ): bool
    {
        return $this->updateBySlugAndNumero(
            $slug,
            $numero,
            [
                'artbook' => trim($dto->artbook),
                'auteur'  => Str::nullableTrim($dto->auteur),
                'serie'   => Str::nullableTrim($dto->serie),
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
     * @return array<string,mixed>
     */
    private function normalizeInsertData(array $data): array
    {
        return [
            'thumbnail' => trim((string) ($data['thumbnail'] ?? '')),
            'extension' => strtolower(trim((string) ($data['extension'] ?? ''))),
            'slug' => $this->normalizeSlug((string) ($data['slug'] ?? '')),
            'numero' => max(1, (int) ($data['numero'] ?? 1)),
            'artbook' => trim((string) ($data['artbook'] ?? '')),
            'auteur' => Str::nullableTrim($data['auteur'] ?? null),
            'serie' => Str::nullableTrim($data['serie'] ?? null),
        ];
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
}
