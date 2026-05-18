<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Validation\Validator;

abstract class FormRequest
{
    protected Validator $validator;
    protected array $data;

    public function __construct(
        protected Request $request
    ) {
        $this->data = $request->postAll();

        $this->validator = new Validator(
            $this->data,
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
        return $this->data;
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