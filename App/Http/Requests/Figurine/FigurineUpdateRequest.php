<?php

declare(strict_types=1);

namespace App\Http\Requests\Figurine;

use App\DTO\Figurine\Inputs\FigurineUpdateDTO;

use Framework\Http\FormRequest;

final class FigurineUpdateRequest extends FormRequest
{
    protected function validate(): void
    {
        $this->validator

            ->required('waifu')
            ->string('waifu')
            ->maxLength('waifu', 150)

            ->required('origin')
            ->string('origin')
            ->maxLength('origin', 150)

            ->required('scale')
            ->string('scale')
            ->maxLength('scale', 10)

            ->nullable('height_cm')
            ->numeric('height_cm')
            ->min('height_cm', 0)

            ->required('company')
            ->string('company')
            ->maxLength('company', 100)

            ->nullable('release_date')
            ->date('release_date')

            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 1000);
    }

    public function dto(): FigurineUpdateDTO
    {
        return FigurineUpdateDTO::fromArray($this->validated());
    }
}