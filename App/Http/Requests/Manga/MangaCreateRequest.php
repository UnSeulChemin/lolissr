<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\DTO\Manga\Inputs\MangaCreateData;

use Framework\Config\UploadConfig;
use Framework\Http\Requests\FormRequest;

final class MangaCreateRequest extends FormRequest
{
    private const STATUTS = ['en_cours', 'termine'];

    // =================================================
    // VALIDATION
    // =================================================

    protected function validate(): void
    {
        $this->validator
            ->required('livre')
            ->string('livre')
            ->maxLength('livre', 150)

            ->nullable('editeur')
            ->string('editeur')
            ->maxLength('editeur', 100)

            ->required('statut')
            ->string('statut')
            ->in('statut', self::STATUTS)

            ->required('slug')
            ->string('slug')
            ->maxLength('slug', 150)
            ->slug('slug', 150)

            ->required('numero')
            ->integer('numero')
            ->min('numero', 1)
            ->max('numero', 999)

            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 255)

            ->nullable('jacquette')
            ->integer('jacquette')
            ->min('jacquette', 1)
            ->max('jacquette', 5)

            ->nullable('livre_note')
            ->integer('livre_note')
            ->min('livre_note', 1)
            ->max('livre_note', 5)

            ->fileRequired('image')
            ->fileOk('image')
            ->imageExtension('image', UploadConfig::allowedExtensions())
            ->imageMime('image', UploadConfig::allowedMimeTypes())
            ->maxFileSize('image', UploadConfig::maxSize());
    }

    // =================================================
    // DTO
    // =================================================

    public function dto(): MangaCreateData
    {
        return MangaCreateData::fromArray($this->validated());
    }
}
