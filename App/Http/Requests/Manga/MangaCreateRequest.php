<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\Core\Config\UploadConfig;
use App\Core\Http\FormRequest;

final class MangaCreateRequest extends FormRequest
{
    protected function validate(): void
    {
        $this->validator
            ->required('livre')
            ->string('livre')
            ->maxLength('livre', 255)

            ->required('slug')
            ->string('slug')
            ->maxLength('slug', 255)

            ->required('numero')
            ->integer('numero')
            ->min('numero', 1)

            ->nullable('commentaire')
            ->string('commentaire')
            ->maxLength('commentaire', 1000)

            ->fileRequired('image')
            ->fileOk('image')
            ->imageExtension('image', UploadConfig::allowedExtensions())
            ->imageMime('image', UploadConfig::allowedMimeTypes())
            ->maxFileSize('image', UploadConfig::maxSize());
    }
}