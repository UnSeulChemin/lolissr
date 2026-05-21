<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\Models\ChinoisVocabulaire;
use App\Models\Model;

final class ChinoisVocabulaireRepository extends Model
{
    protected string $table =
        'chinois_vocabulaire';

    /**
     * @return list<ChinoisVocabulaire>
     */
    public function findByLangue(
        string $langue,
    ): array {
        /** @var list<ChinoisVocabulaire> $vocabulaire */
        $vocabulaire = $this->fetchAll(
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
            [$langue],
            ChinoisVocabulaire::class,
        );

        return $vocabulaire;
    }
}