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

            ->nullable('company')
            ->string('company')
            ->maxLength('company', 100)

            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 1000);
    }

    public function dto(): FigurineUpdateDTO
    {
        return FigurineUpdateDTO::fromArray($this->validated());
    }
}