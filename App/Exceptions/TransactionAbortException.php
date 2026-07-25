<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\DTO\Common\ServiceResult;

use RuntimeException;

final class TransactionAbortException extends RuntimeException
{
    public function __construct(
        public readonly ServiceResult $result
    ) {
        parent::__construct($result->message);
    }
}