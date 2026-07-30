<?php

declare(strict_types=1);

namespace Framework\Validation\Concerns;

use DateTimeImmutable;

trait ValidatesDates
{
    // =========================================
    // DATES
    // =========================================

    public function date(string $field, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->value($field);

        if (! is_string($value))
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} doit être une date valide."
            );

            return $this;
        }

        $value = trim($value);

        foreach (['Y-m-d', 'd/m/Y'] as $format)
        {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            $warningCount = is_array($errors) ? $errors['warning_count'] : 0;
            $errorCount = is_array($errors) ? $errors['error_count'] : 0;

            if (
                $date !== false
                && $warningCount === 0
                && $errorCount === 0
                && $date->format($format) === $value
            ) {
                return $this;
            }
        }

        $this->addError(
            $field,
            $message ?? "Le champ {$field} doit être une date valide."
        );

        return $this;
    }
}