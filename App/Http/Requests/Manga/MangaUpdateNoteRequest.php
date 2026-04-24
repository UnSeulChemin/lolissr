<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\Core\Http\FormRequest;

final class MangaUpdateNoteRequest extends FormRequest
{
    protected function validate(): void
    {
        $this->validator
            ->nullable('jacquette')
            ->integer('jacquette', 'La note jacquette doit être un entier.')
            ->min('jacquette', 1, 'La note jacquette doit être supérieure ou égale à 1.')
            ->max('jacquette', 5, 'La note jacquette doit être inférieure ou égale à 5.')

            ->nullable('livre_note')
            ->integer('livre_note', 'La note du livre doit être un entier.')
            ->min('livre_note', 1, 'La note du livre doit être supérieure ou égale à 1.')
            ->max('livre_note', 5, 'La note du livre doit être inférieure ou égale à 5.');
    }
}