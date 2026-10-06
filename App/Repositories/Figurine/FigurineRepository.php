<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\DTO\Figurine\Inputs\FigurineUpdateData;
use App\Models\Figurine\Figurine;
use App\Repositories\AbstractRepository;

use Framework\Support\Str;

final class FigurineRepository extends AbstractRepository
{
    use \App\Repositories\Collections\Concerns\UpdatesExistingCollection;
    protected string $table = 'figurine';

    public function findOneBySlugAndNumero(string $slug, int $numero): ?Figurine
    {
        /** @var Figurine|null $figurine */
        $figurine = $this->fetchOne(
            "
            SELECT *

            FROM {$this->readTable()}

            WHERE slug = :slug AND {$this->ownerCondition()}
            AND numero = :numero

            LIMIT 1
            ",
            ['slug' => $this->normalizeSlug($slug), 'numero' => $numero],
            Figurine::class
        );

        return $figurine;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): bool
    {
        return parent::insert($this->normalizeInsertData($data));
    }

    public function updateFigurine(string $slug, int $numero, FigurineUpdateData $dto): bool
    {
        return $this->updateExistingBySlugAndNumero(
            $slug,
            $numero,
            [
                'waifu' => $dto->waifu,
                'origin' => $dto->origin,
                'scale' => $dto->scale,
                'height_cm' => $dto->height_cm,
                'company' => $dto->company,
                'release_date' => $dto->release_date,
                'commentaire' => $dto->commentaire
            ]
        );
    }

    public function updateCollectStatus(string $slug, int $numero, bool $collectStatus): bool
    {
        return $this->updateBySlugAndNumero($slug, $numero, ['collect' => (int) $collectStatus]);
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteExistingById($id);
    }

    public function claimCollectReward(int $id): bool
    {
        $statement = $this->query(
            "
            UPDATE {$this->table()}

            SET collect_rewarded = 1

            WHERE id = :id AND {$this->ownerCondition()}
            AND collect_rewarded = 0
            ",
            ['id' => $id]
        );

        return $statement !== false && $statement->rowCount() === 1;
    }

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

    private function normalizeSlug(string $slug): string
    {
        return Str::slug($slug);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateBySlugAndNumero(string $slug, int $numero, array $data): bool
    {
        return $this->update($data, ['slug' => $this->normalizeSlug($slug), 'numero' => $numero]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
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
            'scale' => trim((string) ($data['scale'] ?? '')),
            'height_cm' => isset($data['height_cm']) && $data['height_cm'] !== ''
                ? (float) $data['height_cm']
                : null,
            'company' => trim((string) ($data['company'] ?? '')),
            'release_date' => Str::nullableTrim($data['release_date'] ?? null),

            'commentaire' => Str::nullableTrim($data['commentaire'] ?? null)
        ];
    }
}
