<?php

declare(strict_types=1);

namespace Framework\Database;

use Framework\Application\App;
use Framework\Config\DatabaseConfig;
use Framework\Support\Logger;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Database extends PDO
{
    // =========================================
    // CONNEXION
    // =========================================

    public function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DatabaseConfig::host(),
            DatabaseConfig::port(),
            DatabaseConfig::name(),
            DatabaseConfig::charset(),
        );

        try
        {
            parent::__construct(
                $dsn,
                DatabaseConfig::user(),
                DatabaseConfig::pass(),
                [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
        }
        catch (PDOException $exception)
        {
            Logger::exception($exception, ['type' => 'database_connection']);

            throw new RuntimeException(
                App::debug()
                    ? $exception->getMessage()
                    : 'Erreur de connexion à la base de données.',
                previous: $exception,
            );
        }
    }

    // =========================================
    // TRANSACTIONS
    // =========================================

    public function startTransaction(): void
    {
        if ($this->inTransaction())
        {
            return;
        }

        $this->beginTransaction();
    }

    public function commitTransaction(): void
    {
        if (! $this->inTransaction())
        {
            return;
        }

        $this->commit();
    }

    public function rollbackTransaction(): void
    {
        if (! $this->inTransaction())
        {
            return;
        }

        $this->rollBack();
    }

    public function transaction(callable $callback): mixed
    {
        $this->startTransaction();

        try
        {
            $result = $callback();

            $this->commitTransaction();

            return $result;
        }
        catch (Throwable $exception)
        {
            $this->rollbackTransaction();

            throw $exception;
        }
    }
}
