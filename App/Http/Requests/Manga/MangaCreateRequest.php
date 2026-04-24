<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\Core\Http\FormRequest;

final class MangaCreateRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'livre' => ['required'],
            'slug' => ['required'],
            'numero' => ['required'],

            'image' => [
                'required',
                'file',
                'image'
            ]
        ];
    }
}