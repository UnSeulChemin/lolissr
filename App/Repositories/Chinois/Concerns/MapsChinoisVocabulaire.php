<?php

declare(strict_types=1);

namespace App\Repositories\Chinois\Concerns;

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;

use stdClass;

trait MapsChinoisVocabulaire
{
    private const SELECT_FIELDS = '
        id,
        langue,
        mot,
        pinyin,
        type,
        traduction,
        exemple,
        maitrise,
        xp_rewarded
    ';

    private function mapRowToDto(stdClass $row): ChinoisVocabulaireData
    {
        $exemple = $row->exemple !== null ? trim((string) $row->exemple) : '';
        $maitrise = (bool) $row->maitrise;

        return new ChinoisVocabulaireData(
            id: (int) $row->id,
            langue: (string) $row->langue,
            mot: (string) $row->mot,
            pinyin: (string) $row->pinyin,
            type: (string) $row->type,
            traduction: (string) $row->traduction,
            exemple: $exemple,
            maitrise: $maitrise,
            xpRewarded: (bool) $row->xp_rewarded,
            hasExemple: $exemple !== '',
            masteredClass: $maitrise ? 'active' : '',
            masteredValue: $maitrise ? '1' : '0',
            masteredPressed: $maitrise ? 'true' : 'false',
            masteredLabel: $maitrise ? 'Retirer la maîtrise' : 'Marquer comme maîtrisé'
        );
    }
}