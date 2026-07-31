<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\DTO\Manga\Inputs\ArtbookUpdateDTO;

use Framework\Http\FormRequest;

final class ArtbookUpdateRequest extends FormRequest
{
    private const SOURCE_TYPES = ['auteur', 'serie'];

    // =========================================
    // VALIDATION
    // =========================================

    protected function validate(): void
    {
        $this->validator
            ->required('artbook')
            ->string('artbook')
            ->maxLength('artbook', 150)

            ->required('type_source')
            ->string('type_source')
            ->in('type_source', self::SOURCE_TYPES)

            ->required('source')
            ->string('source')
            ->maxLength('source', 100)

            ->required('company')
            ->string('company')
            ->maxLength('company', 100)

            ->nullable('release_date')
            ->date('release_date')

            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 255);
    }

    // =========================================
    // DTO
    // =========================================

    public function dto(): ArtbookUpdateDTO
    {
        return ArtbookUpdateDTO::fromArray($this->validated());
    }
}