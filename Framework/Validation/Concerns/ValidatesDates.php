<?php

declare(strict_types=1);

namespace Framework\Validation\Concerns;

use Framework\Support\Dates\DateNormalizer;

trait ValidatesDates
{
    // =================================================
    // DATES
    // =================================================

    public function date(string $field, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->value($field);

        if (is_string($value) && DateNormalizer::normalize($value) !== null)
        {
            return $this;
        }

        $this->addError(
            $field,
            $message ?? "Le champ {$field} doit être une date valide."
        );

        return $this;
    }
}
