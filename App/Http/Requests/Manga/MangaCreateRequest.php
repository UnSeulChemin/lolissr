<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\DTO\Manga\Inputs\MangaCreateDTO;
use Framework\Config\UploadConfig;
use Framework\Http\FormRequest;

final class MangaCreateRequest extends FormRequest
{
    private const STATUTS = [
        'en_cours',
        'termine',
    ];

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

            ->required('numero')
            ->integer('numero')
            ->min('numero', 1)
            ->max('numero', 999)

            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 1000)

            ->fileRequired('image')
            ->fileOk('image')
            ->imageExtension(
                'image',
                UploadConfig::allowedExtensions(),
            )
            ->imageMime(
                'image',
                UploadConfig::allowedMimeTypes(),
            )
            ->maxFileSize(
                'image',
                UploadConfig::maxSize(),
            );
    }

    public function dto(): MangaCreateDTO
    {
        return MangaCreateDTO::fromPost(
            $this->validated(),
        );
    }
}