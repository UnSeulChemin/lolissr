<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\DTO\Figurine\Responses\FigurineData;
use App\Models\Figurine;
use App\Models\Model;

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
     * @return list<FigurineData>
     */
    public function findAllDto(): array
    {
        return $this->mapResultsToDto($this->findAll());
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

    public function findDtoBySlug(string $slug): ?FigurineData
    {
        $figurine = $this->findBySlug($slug);

        if ($figurine === null)
        {
            return null;
        }

        return $this->mapToDto($figurine);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function insert(array $data): bool
    {
        return parent::insert($this->normalizeInsertData($data));
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapToDto(Figurine $figurine): FigurineData
    {
        return new FigurineData(
            id: $figurine->id,

            slug: $figurine->slug,
            waifu: $figurine->waifu,
            company: $figurine->company,

            thumbnail: $figurine->thumbnail !== '' ? $figurine->thumbnail : null,
            extension: $figurine->extension !== '' ? $figurine->extension : null,

            commentaire: $figurine->commentaire,
        );
    }

    /**
     * @param list<Figurine> $figurines
     * @return list<FigurineData>
     */
    private function mapResultsToDto(array $figurines): array
    {
        return array_map($this->mapToDto(...), $figurines);
    }

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
            'waifu' => trim((string) ($data['waifu'] ?? '')),
            'company' => trim((string) ($data['company'] ?? '')),
            'commentaire' => Str::nullableTrim($data['commentaire'] ?? null),
        ];
    }
}