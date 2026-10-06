<?php

declare(strict_types=1);

namespace App\Http\Requests\Peluche;

use App\DTO\Peluche\Inputs\PelucheUpdateData;

use Framework\Http\Requests\FormRequest;

final class PelucheUpdateRequest extends FormRequest
{
    // =================================================
    // VALIDATION
    // =================================================

    protected function validate(): void
    {
        $this->validator
            ->required('waifu')
            ->string('waifu')
            ->maxLength('waifu', 100)

            ->required('origin')
            ->string('origin')
            ->maxLength('origin', 150)

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

    public function dto(): PelucheUpdateData
    {
        return PelucheUpdateData::fromArray($this->validated());
    }
}