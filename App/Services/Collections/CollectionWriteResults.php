<?php

declare(strict_types=1);

namespace App\Services\Collections;

use App\DTO\Common\ServiceResult;
use Framework\Support\Logger;

trait CollectionWriteResults
{
    /** @param array<string, mixed> $data */
    private function success(
        string $message,
        array $data = [],
        int $status = 200
    ): ServiceResult {
        return ServiceResult::success(
            message: $message,
            data: $data,
            status: $status
        );
    }

    /** @param array<string, mixed> $data */
    private function error(
        string $message,
        int $status = 500,
        array $data = []
    ): ServiceResult {
        return ServiceResult::error(
            message: $message,
            data: $data,
            status: $status
        );
    }

    private function writeFailed(
        bool $result,
        string $action,
        string $slug,
        int $numero,
        string $message
    ): ?ServiceResult {
        if ($result)
        {
            return null;
        }

        $this->logFailure($action, $slug, $numero);

        return $this->error($message);
    }

    private function logFailure(string $action, string $slug, int $numero): void
    {
        Logger::error("{$action} échoué slug={$slug} numero={$numero}");
    }

}
