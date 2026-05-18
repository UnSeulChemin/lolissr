<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Validation\Validator;

abstract class FormRequest
{
    protected Validator $validator;

    public function __construct(
        protected Request $request
    ) {
        $this->validator = new Validator(
            $request->post(),
            $request->files()
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
        return $this->request->post();
    }

    public function files(): array
    {
        return $this->request->files();
    }

    protected function input(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->request->input($key, $default);
    }
}