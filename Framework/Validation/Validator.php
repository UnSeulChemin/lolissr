<?php

declare(strict_types=1);

namespace Framework\Validation;

use Framework\Validation\Concerns\ValidatesDates;
use Framework\Validation\Concerns\ValidatesFiles;
use Framework\Validation\Concerns\ValidatesValues;

use finfo;

final class Validator
{
    use ValidatesDates;
    use ValidatesFiles;
    use ValidatesValues;

    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @var array<string, mixed>
     */
    private array $files;

    /**
     * @var array<string, string>
     */
    private array $errors = [];

    /**
     * @var array<string, true>
     */
    private array $fields = [];

    /**
     * @var array<string, true>
     */
    private array $nullable = [];

    /**
     * @var array<string, int|null>
     */
    private array $integerCache = [];

    /**
     * @var array<string, int|float|null>
     */
    private array $numericCache = [];

    private ?finfo $finfo = null;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     */
    public function __construct(array $data, array $files = [])
    {
        $this->data = $data;
        $this->files = $files;
    }

    // =================================================
    // RÉSULTATS
    // =================================================

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        $validated = [];

        foreach ($this->fields as $field => $_)
        {
            if (array_key_exists($field, $this->data))
            {
                $validated[$field] = $this->isNullableAndEmpty($field)
                    ? null
                    : $this->data[$field];
            }
        }

        return $validated;
    }

    // =================================================
    // CHAMPS
    // =================================================

    private function rememberField(string $field): void
    {
        $this->fields[$field] = true;
    }

    private function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    private function addError(string $field, string $message): void
    {
        if (! $this->hasError($field))
        {
            $this->errors[$field] = $message;
        }
    }

    private function value(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    private function isEmptyValue(mixed $value): bool
    {
        if ($value === null)
        {
            return true;
        }

        if (is_string($value))
        {
            return trim($value) === '';
        }

        return is_array($value) && $value === [];
    }

    private function isNullableAndEmpty(string $field): bool
    {
        $value = $this->value($field);

        return isset($this->nullable[$field])
            && ($value === null
                || (is_string($value) && trim($value, " \t\n\r\x0B") === ''));
    }

    private function shouldSkip(string $field): bool
    {
        return $this->hasError($field) || $this->isNullableAndEmpty($field);
    }

    // =================================================
    // CONVERSIONS
    // =================================================

    private function integerValue(string $field): ?int
    {
        if (array_key_exists($field, $this->integerCache))
        {
            return $this->integerCache[$field];
        }

        $value = $this->value($field);
        $integer = is_int($value) || is_string($value)
            ? filter_var($value, FILTER_VALIDATE_INT)
            : false;

        return $this->integerCache[$field] = $integer !== false
            ? $integer
            : null;
    }

    private function numericValue(string $field): int|float|null
    {
        if (array_key_exists($field, $this->numericCache))
        {
            return $this->numericCache[$field];
        }

        $value = $this->value($field);

        if (is_int($value))
        {
            return $this->numericCache[$field] = $value;
        }

        if (is_float($value))
        {
            return $this->numericCache[$field] = is_finite($value) ? $value : null;
        }

        if (! is_string($value))
        {
            return $this->numericCache[$field] = null;
        }

        $value = trim($value);

        $integer = filter_var($value, FILTER_VALIDATE_INT);
        if ($integer !== false)
        {
            return $this->numericCache[$field] = $integer;
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($float !== false && is_finite($float))
        {
            return $this->numericCache[$field] = $float;
        }

        return $this->numericCache[$field] = null;
    }

    // =================================================
    // FICHIERS
    // =================================================

    /**
     * @return array<string, mixed>|null
     */
    private function fileData(string $field): ?array
    {
        $file = $this->files[$field] ?? null;

        return is_array($file) ? $file : null;
    }

    private function hasUploadedFile(string $field): bool
    {
        $file = $this->fileData($field);

        if ($file === null)
        {
            return false;
        }

        $temporaryPath = $file['tmp_name'] ?? null;
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        return $error !== UPLOAD_ERR_NO_FILE
            && is_string($temporaryPath)
            && $temporaryPath !== '';
    }

    private function shouldSkipFile(string $field): bool
    {
        return $this->hasError($field) || ! $this->hasUploadedFile($field);
    }
}
