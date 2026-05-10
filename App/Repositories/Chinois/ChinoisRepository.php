<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\Models\Model;

final class ChinoisRepository extends Model
{
    protected string $table = 'chinois_vocabulaire';

    public function findByLangue(string $langue): array
    {
        $query = $this->requete(
            "SELECT
                id,
                langue,
                mot,
                pinyin,
                type,
                traduction,
                exemple,
                created_at
            FROM {$this->getTable()}
            WHERE langue = ?
            ORDER BY id DESC",
            [$langue]
        );

        return $query ? $query->fetchAll() : [];
    }
}