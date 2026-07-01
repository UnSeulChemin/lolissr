<?php

declare(strict_types=1);

namespace App\Repositories\Chinois\Concerns;

trait HasDtoMapper
{
    /**
     * @template TRow of object
     * @template TDto
     *
     * @param list<TRow> $results
     * @return list<TDto>
     */
    private function mapResultsToDto(array $results): array
    {
        return array_map($this->mapRowToDto(...), $results);
    }
}