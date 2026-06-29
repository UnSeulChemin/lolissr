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

            ->nullable('company')
            ->string('company')
            ->maxLength('company', 100)

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