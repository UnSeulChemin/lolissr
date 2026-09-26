<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\DTO\Common\ServiceResult;
use App\DTO\Upload\UploadThumbnailData;
use Framework\Database\Database;
use Framework\Support\Logger;
use PDOException;

final readonly class CollectionCreationService
{
    public function __construct(private Database $database, private ThumbnailManager $thumbnails)
    {
    }

    /**
     * @param array<string, mixed> $files
     * @param callable(UploadThumbnailData): ServiceResult $persist
     */
    public function create(
        string $collection,
        string $name,
        int $numero,
        array $files,
        callable $persist,
        string $duplicateMessage
    ): ServiceResult {
        // File validation and disk I/O do not require an open database transaction.
        $upload = $this->thumbnails->upload($collection, $name, $numero, $files);
        if ($upload instanceof ServiceResult) return $upload;

        $committed = false;
        try
        {
            $result = $this->database->transaction(fn (): ServiceResult => $persist($upload));
            $committed = $result->success;
            return $result;
        }
        catch (PDOException $exception)
        {
            if ($exception->getCode() === '23000' && ($exception->errorInfo[1] ?? null) === 1062)
            {
                return ServiceResult::error($duplicateMessage, status: 409);
            }
            throw $exception;
        }
        finally
        {
            if (! $committed)
            {
                try
                {
                    if (! $this->thumbnails->rollback($upload))
                    {
                        Logger::warning('Creation failed: image cleanup failed', ['collection' => $collection, 'numero' => $numero]);
                    }
                }
                catch (\Throwable $cleanupError)
                {
                    // Do not mask the original insertion/commit failure.
                    Logger::exception($cleanupError, ['collection' => $collection, 'action' => 'image cleanup']);
                }
            }
        }
    }
}
