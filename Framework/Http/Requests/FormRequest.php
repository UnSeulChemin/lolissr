<?php

declare(strict_types=1);

namespace Framework\Http\Requests;

use Framework\Validation\Validator;

abstract class FormRequest
{
    protected Validator $validator;

    public function __construct(protected readonly Request $request)
    {
        $this->validator = new Validator($this->request->postAll(), $this->request->files());

        $this->validate();
    }

    // =================================================
    // VALIDATION
    // =================================================

    abstract protected function validate(): void;

    abstract public function dto(): object;

    final public function fails(): bool
    {
        return $this->validator->fails();
    }

    /**
     * @return array<string, string>
     */
    final public function errors(): array
    {
        return $this->validator->errors();
    }

    /**
     * @return array<string, mixed>
     */
    final public function validated(): array
    {
        if ($this->fails())
        {
            return [];
        }

        return $this->validator->validated();
    }

    // =================================================
    // DONNÉES
    // =================================================

    /**
     * @return array<string, mixed>
     */
    final public function files(): array
    {
        return $this->request->files();
    }

    // =================================================
    // UTILITAIRES
    // =================================================

    final protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }
}