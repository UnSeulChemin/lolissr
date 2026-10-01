<?php

declare(strict_types=1);

namespace App\Repositories\Collections\Concerns;

use Framework\Http\Exceptions\NotFoundException;
use LogicException;

trait UpdatesExistingCollection
{
    /** @param array<string, mixed> $data */
    private function updateExistingBySlugAndNumero(string $slug, int $numero, array $data): bool
    {
        if (! $this->db->inTransaction())
        {
            throw new LogicException('Collection updates require a transaction.');
        }

        $current = $this->fetchOne(
            "SELECT id FROM {$this->table()} WHERE slug = :slug AND numero = :numero LIMIT 1 FOR UPDATE",
            ['slug' => $this->normalizeSlug($slug), 'numero' => $numero]
        );
        if ($current === null)
        {
            throw new NotFoundException('Élément introuvable ou déjà supprimé');
        }

        // execute() stays successful for an unchanged row; existence is checked under lock.
        return $this->update($data, ['id' => (int) $current->id]);
    }
}
