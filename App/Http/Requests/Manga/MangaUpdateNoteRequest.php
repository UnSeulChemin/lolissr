<?php

declare(strict_types=1);

namespace App\Http\Requests\Manga;

use App\Core\Http\FormRequest;

final class MangaUpdateNoteRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'note' => [
                'required',
                'integer',
                'min:1',
                'max:5'
            ]
        ];
    }
}