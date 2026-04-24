<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\Core\Http\FormRequest;

final class MangaUpdateRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'commentaire' => ['nullable'],

            'livre_note' => [
                'nullable',
                'integer',
                'min:1',
                'max:5'
            ],

            'jacquette' => [
                'nullable',
                'integer',
                'min:1',
                'max:5'
            ]
        ];
    }
}