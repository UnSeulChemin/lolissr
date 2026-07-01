<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\Models\Model;
use App\Repositories\Chinois\Concerns\HasLearningStats;

final class ChinoisVocabulaireStatsRepository extends Model
{
    use HasLearningStats;

    protected string $table = 'chinois_vocabulaire';
}