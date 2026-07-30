<?php

declare(strict_types=1);

namespace Framework\Database;

use Framework\Application\App;
use Framework\Config\DatabaseConfig;
use Framework\Debug\Profiler;
use Framework\Support\Logger;

use LogicException;
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
        Profiler::increment('database.connection');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DatabaseConfig::host(),
            DatabaseConfig::port(),
            DatabaseConfig::name(),
            DatabaseConfig::charset()
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
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        }
        catch (PDOException $exception)
        {
            Logger::exception(
                $exception,
                [
                    'type' => 'database_connection'
                ]
            );

            throw new RuntimeException(
                App::debug()
                    ? $exception->getMessage()
                    : 'Erreur de connexion à la base de données.',
                previous: $exception
            );
        }
    }

    // =========================================
    // TRANSACTIONS
    // =========================================

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        if ($this->inTransaction())
        {
            throw new LogicException(
                'Les transactions imbriquées ne sont pas supportées.'
            );
        }

        Profiler::start('database.transaction');

        try
        {
            if (! $this->beginTransaction())
            {
                throw new RuntimeException(
                    'Impossible de démarrer la transaction.'
                );
            }

            try
            {
                $result = $callback();

                if (! $this->commit())
                {
                    throw new RuntimeException(
                        'Impossible de valider la transaction.'
                    );
                }

                return $result;
            }
            catch (Throwable $exception)
            {
                $this->rollbackSafely($exception);

                throw $exception;
            }
        }
        finally
        {
            Profiler::end('database.transaction');
        }
    }

    // =========================================
    // ROLLBACK
    // =========================================

    private function rollbackSafely(Throwable $originalException): void
    {
        if (! $this->inTransaction())
        {
            return;
        }

        try
        {
            if (! $this->rollBack())
            {
                Logger::error(
                    'Database rollback failed',
                    [
                        'original_error' => $originalException->getMessage()
                    ]
                );
            }
        }
        catch (Throwable $rollbackException)
        {
            Logger::exception(
                $rollbackException,
                [
                    'type' => 'database_rollback',
                    'original_error' => $originalException->getMessage()
                ]
            );
        }
    }
}