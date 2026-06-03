<?php

declare(strict_types=1);

namespace App\Http\Requests\Chinois;

use App\DTO\Chinois\Inputs\ChinoisVocabulaireCreateDTO;
use Framework\Http\FormRequest;

final class ChinoisVocabulaireCreateRequest extends FormRequest
{
    private const LANGUES = [
        'mandarin',
        'jinyu',
    ];

    protected function validate(): void
    {
        $this->validator

            ->required('langue')
            ->string('langue')
            ->in('langue', self::LANGUES)

            ->required('mot')
            ->string('mot')
            ->maxLength('mot', 255)

            ->required('pinyin')
            ->string('pinyin')
            ->maxLength('pinyin', 255)

            ->required('type')
            ->string('type')
            ->maxLength('type', 100)

            ->required('traduction')
            ->string('traduction')

            ->nullable('exemple')
            ->string('exemple');
    }

    public function dto(): ChinoisVocabulaireCreateDTO
    {
        return ChinoisVocabulaireCreateDTO::fromPost(
            $this->validated(),
        );
    }
}