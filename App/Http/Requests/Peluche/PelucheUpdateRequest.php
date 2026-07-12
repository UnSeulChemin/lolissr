<?php

declare(strict_types=1);

namespace App\Http\Requests\Peluche;

use App\DTO\Peluche\Inputs\PelucheUpdateDTO;

use Framework\Http\FormRequest;

final class PelucheUpdateRequest extends FormRequest
{
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
            ->string('release_date')

            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 1000);
    }

    public function dto(): PelucheUpdateDTO
    {
        return PelucheUpdateDTO::fromArray(
            $this->validated()
        );
    }
}