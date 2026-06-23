<?php

declare(strict_types=1);

namespace App\Repositories\Sql;

use App\Models\Model;

use stdClass;

final class SqlRepository extends Model
{
    /**
     * @return list<stdClass>
     */
    public function executeQuery(string $sql): array
    {
        return $this->fetchAll(trim($sql), [], stdClass::class);
    }
}
