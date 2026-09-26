<?php

declare(strict_types=1);

namespace Framework\Database;

interface TransactionResult
{
    public function shouldCommit(): bool;
}
