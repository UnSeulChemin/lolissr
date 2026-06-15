<?php

declare(strict_types=1);

namespace App\Repositories\Sql;

use App\Models\Model;

final class SqlRepository extends Model
{
    /**
     * @return list<object>
     */
    public function executeQuery(
        string $sql,
    ): array {
        return $this->fetchAll(
            trim($sql),
        );
    }
}