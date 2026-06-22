<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\DTO\Manga\Inputs\MangaUpdateDTO;

use Framework\Http\FormRequest;

final class MangaUpdateRequest extends FormRequest
{
    private const STATUTS = ['en_cours', 'termine'];

    protected function validate(): void
    {
        $this->validator

            ->nullable('editeur')
            ->string('editeur')
            ->maxLength('editeur', 100)

            ->required('statut')
            ->string('statut')
            ->in('statut', self::STATUTS)

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
        return MangaUpdateDTO::fromArray($this->validated());
    }
}
