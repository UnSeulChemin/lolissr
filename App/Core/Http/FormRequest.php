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

        $this->validate();
    }

    abstract protected function validate(): void;

    public function passes(): bool
    {
        return !$this->validator->fails();
    }

    public function fails(): bool
    {
        return $this->validator->fails();
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