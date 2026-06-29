<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\DTO\Manga\Inputs\ArtbookUpdateDTO;

use Framework\Http\FormRequest;

final class ArtbookUpdateRequest extends FormRequest
{
    protected function validate(): void
    {
        $this->validator

            ->nullable('auteur')
            ->string('auteur')
            ->maxLength('auteur', 100)

            ->nullable('serie')
            ->string('serie')
            ->maxLength('serie', 100);
    }

    public function dto(): ArtbookUpdateDTO
    {
        return ArtbookUpdateDTO::fromArray(
            $this->validated()
        );
    }
}