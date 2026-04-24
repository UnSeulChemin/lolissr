<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Validation\Validator;

abstract class FormRequest
{
    protected Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator(
            Request::allPost(),
            Request::allFiles()
        );

        $this->validator->rules($this->rules());
        $this->validator->validate();
    }

    abstract protected function rules(): array;

    public function passes(): bool
    {
        return empty($this->errors());
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->validator->errors();
    }

    public function data(): array
    {
        return Request::allPost();
    }

    public function files(): array
    {
        return Request::allFiles();
    }
}