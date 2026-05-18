<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\Core\Http\FormRequest;
use App\DTO\Manga\MangaUpdateDTO;

final class MangaUpdateRequest extends FormRequest
{
    protected function validate(): void
    {
        $this->validator
            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 1000)

            ->nullable('livre_note')
            ->integer('livre_note')
            ->min('livre_note', 1)
            ->max('livre_note', 5)

            ->nullable('jacquette')
            ->integer('jacquette')
            ->min('jacquette', 1)
            ->max('jacquette', 5);
    }

    public function dto(): MangaUpdateDTO
    {
        return MangaUpdateDTO::fromPost(
            $this->validated()
        );
    }
}