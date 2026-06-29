<?php

declare(strict_types=1);

namespace App\Http\Requests\Figurine;

use App\DTO\Figurine\Inputs\FigurineCreateDTO;

use Framework\Config\UploadConfig;
use Framework\Http\FormRequest;

final class FigurineCreateRequest extends FormRequest
{
    protected function validate(): void
    {
        $this->validator

            ->required('waifu')
            ->string('waifu')
            ->maxLength('waifu', 100)

            ->required('company')
            ->string('company')
            ->maxLength('company', 100)

            ->required('slug')
            ->string('slug')
            ->maxLength('slug', 100)

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

    public function dto(): FigurineCreateDTO
    {
        return FigurineCreateDTO::fromArray($this->validated());
    }
}