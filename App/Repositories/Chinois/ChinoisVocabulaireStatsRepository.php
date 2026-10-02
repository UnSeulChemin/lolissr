<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\Repositories\AbstractRepository;
use App\Repositories\Chinois\Concerns\HasLearningStats;

final class ChinoisVocabulaireStatsRepository extends AbstractRepository
{
    use HasLearningStats;

    protected string $table = 'chinois_vocabulaire';
}