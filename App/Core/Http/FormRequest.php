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

    final public function passes(): bool
    {
        return !$this->fails();
    }

    final public function fails(): bool
    {
        return $this->validator->fails();
    }

    final public function errors(): array
    {
        return $this->validator->errors();
    }

    final public function validated(): array
    {
        return $this->request->postAll();
    }

    final public function files(): array
    {
        return $this->request->files();
    }

    final public function all(): array
    {
        return $this->request->all();
    }

    final protected function input(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->request->input(
            $key,
            $default
        );
    }
}