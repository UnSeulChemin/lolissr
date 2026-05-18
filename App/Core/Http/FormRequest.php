<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Validation\Validator;

abstract class FormRequest
{
    protected Validator $validator;

    public function __construct(
        protected readonly Request $request
    ) {
        $this->validator = new Validator(
            $this->request->postAll(),
            $this->request->files()
        );

        $this->validate();
    }

    abstract protected function validate(): void;

    abstract public function dto(): object;

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

    public function files(): array
    {
        return $this->request->files();
    }

    public function all(): array
    {
        return $this->request->postAll();
    }

    protected function input(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->request->input(
            $key,
            $default
        );
    }
}