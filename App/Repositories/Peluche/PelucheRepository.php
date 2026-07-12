<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\DTO\Peluche\Inputs\PelucheUpdateDTO;
use App\Models\Model;
use App\Models\Peluche;

use Framework\Support\Str;

final class PelucheRepository extends Model
{
    protected string $table = 'peluche';

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

    /**
     * @param array<string,mixed> $data
     */
    public function insert(array $data): bool
    {
        return parent::insert($this->normalizeInsertData($data));
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
                'waifu' => $dto->waifu,
                'origin' => $dto->origin,
                'company' => $dto->company,
                'release_date' => $dto->release_date,
                'commentaire' => $dto->commentaire,
            ]
        );
    }

    public function updateCollectStatus(
        string $slug,
        int $numero,
        bool $collectStatus
    ): bool
    {
        return $this->updateBySlugAndNumero(
            $slug,
            $numero,
            [
                'collect' => (int) $collectStatus,
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

    /**
     * @return list<Peluche>
     */
    public function findCollectedWithoutReward(): array
    {
        /** @var list<Peluche> $peluches */
        $peluches = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            WHERE collect = 1
            AND collect_rewarded = 0
            ",
            [],
            Peluche::class
        );

        return $peluches;
    }

    public function markXpRewarded(int $id): bool
    {
        return $this->update(
            ['collect_rewarded' => 1],
            ['id' => $id]
        );
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
            'origin' => trim((string) ($data['origin'] ?? '')),
            'company' => trim((string) ($data['company'] ?? '')),
            'release_date' => Str::nullableTrim($data['release_date'] ?? null),

            'commentaire' => Str::nullableTrim($data['commentaire'] ?? null),
        ];
    }
}