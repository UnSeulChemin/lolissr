<?php

declare(strict_types=1);

namespace App\Http\Requests\Chinois;

use App\DTO\Chinois\Inputs\ChinoisGrammaireCreateData;

use Framework\Http\FormRequest;

final class ChinoisGrammaireCreateRequest extends FormRequest
{
    private const NIVEAUX = ['HSK1', 'HSK2', 'HSK3', 'HSK4'];

    // =========================================
    // VALIDATION
    // =========================================

    protected function validate(): void
    {
        $this->validator
            ->required('niveau')
            ->string('niveau')
            ->in('niveau', self::NIVEAUX)

            ->required('titre')
            ->string('titre')
            ->maxLength('titre', 255)

            ->required('structure')
            ->string('structure')
            ->maxLength('structure', 255)

            ->nullable('abreviation')
            ->string('abreviation')
            ->maxLength('abreviation', 100)

            ->required('phrase')
            ->string('phrase')
            ->maxLength('phrase', 255)

            ->required('pinyin')
            ->string('pinyin')
            ->maxLength('pinyin', 255)

            ->required('traduction')
            ->string('traduction')
            ->maxLength('traduction', 255)

            ->required('explication')
            ->string('explication')

            ->required('section')
            ->string('section')
            ->maxLength('section', 100)

            ->required('categorie')
            ->string('categorie')
            ->maxLength('categorie', 100);
    }

    // =========================================
    // DTO
    // =========================================

    public function dto(): ChinoisGrammaireCreateData
    {
        return ChinoisGrammaireCreateData::fromArray($this->validated());
    }
}