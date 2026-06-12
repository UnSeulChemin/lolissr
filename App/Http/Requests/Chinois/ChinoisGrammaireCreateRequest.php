<?php

declare(strict_types=1);

namespace App\Http\Requests\Chinois;

use App\DTO\Chinois\Inputs\ChinoisGrammaireCreateDTO;
use Framework\Http\FormRequest;

final class ChinoisGrammaireCreateRequest extends FormRequest
{
    private const NIVEAUX = [
        'HSK1',
        'HSK2',
        'HSK3',
        'HSK4',
    ];

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

            ->nullable('abreviation')
            ->string('abreviation')
            ->maxLength('abreviation', 100)

            ->required('phrase')
            ->string('phrase')

            ->required('pinyin')
            ->string('pinyin')

            ->required('traduction')
            ->string('traduction')

            ->nullable('explication')
            ->string('explication')

            ->required('section')
            ->string('section')
            ->maxLength('section', 255)

            ->required('categorie')
            ->string('categorie')
            ->maxLength('categorie', 255);
    }

    public function dto(): ChinoisGrammaireCreateDTO
    {
        return ChinoisGrammaireCreateDTO::fromArray(
            $this->validated(),
        );
    }
}