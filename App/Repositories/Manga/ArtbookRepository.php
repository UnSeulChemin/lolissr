<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Artbook;
use App\Models\Model;

final class ArtbookRepository extends Model
{
    protected string $table =
        'artbook';

    /**
     * @return list<Artbook>
     */
    public function findAll(): array
    {
        /** @var list<Artbook> $artbooks */
        $artbooks =
            $this->fetchAll(
                "
                SELECT *
                FROM {$this->table()}
                ORDER BY created_at DESC
                ",
                [],
                Artbook::class,
            );

        return $artbooks;
    }
}