<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\DTO\Manga\Inputs\ArtbookCreateDTO;

use Framework\Config\UploadConfig;
use Framework\Http\FormRequest;

final class ArtbookCreateRequest extends FormRequest
{
    protected function validate(): void
    {
        $this->validator

            ->required('artbook')
            ->string('artbook')
            ->maxLength('artbook', 150)

            ->required('type_source')
            ->string('type_source')
            ->in('type_source', ['auteur', 'serie'])

            ->required('source')
            ->string('source')
            ->maxLength('source', 100)

            ->required('company')
            ->string('company')
            ->maxLength('company', 100)

            ->nullable('release_date')
            ->date('release_date')

            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 255)

            ->required('slug')
            ->string('slug')
            ->maxLength('slug', 150)

            ->required('numero')
            ->integer('numero')
            ->min('numero', 1)
            ->max('numero', 999)

            ->fileRequired('image')
            ->fileOk('image')
            ->imageExtension('image', UploadConfig::allowedExtensions())
            ->imageMime('image', UploadConfig::allowedMimeTypes())
            ->maxFileSize('image', UploadConfig::maxSize());
    }

    public function dto(): ArtbookCreateDTO
    {
        return ArtbookCreateDTO::fromArray($this->validated());
    }
}