<?php

declare(strict_types=1);

namespace App\Http\Requests\Artbook;

use App\DTO\Artbook\Inputs\ArtbookUpdateData;

use Framework\Http\FormRequest;

final class ArtbookUpdateRequest extends FormRequest
{
    // =================================================
    // VALIDATION
    // =================================================

    protected function validate(): void
    {
        $this->validator
            ->required('artbook')
            ->string('artbook')
            ->maxLength('artbook', 150)

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

    // =================================================
    // DTO
    // =================================================

    public function dto(): ArtbookUpdateData
    {
        return ArtbookUpdateData::fromArray($this->validated());
    }
}