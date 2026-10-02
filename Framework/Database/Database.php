<?php

declare(strict_types=1);

namespace Framework\Database;

use Framework\Config\ApplicationConfig;
use Framework\Config\DatabaseConfig;
use Framework\Debug\Profiler;
use Framework\Logging\Logger;

use LogicException;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Database extends PDO
{
    /** @var list<callable(): void> */
    private array $rollbackCallbacks = [];
    private bool $managedTransaction = false;

    public function onRollback(callable $callback): void
    {
        if (! $this->managedTransaction || ! $this->inTransaction())
        {
            throw new LogicException('Rollback callbacks require a managed transaction.');
        }
        $this->rollbackCallbacks[] = $callback;
    }

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

            if (ApplicationConfig::isTesting())
            {
                $this->exec('SET SESSION TRANSACTION READ ONLY');
            }
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
                ApplicationConfig::debug()
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
        $committed = false;
        $this->managedTransaction = true;

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

                // Explicit failure results roll back while preserving their public error payload.
                if ($result instanceof TransactionResult && ! $result->shouldCommit())
                {
                    if (! $this->rollBack())
                    {
                        throw new RuntimeException('Impossible d’annuler la transaction.');
                    }
                    return $result;
                }

                if (! $this->commit())
                {
                    throw new RuntimeException(
                        'Impossible de valider la transaction.'
                    );
                }

                $committed = true;
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
            $callbacks = array_reverse($this->rollbackCallbacks);
            $this->rollbackCallbacks = [];
            $this->managedTransaction = false;
            if (! $committed)
            {
                foreach ($callbacks as $restore)
                {
                    try
                    {
                        $restore();
                    }
                    catch (Throwable $error)
                    {
                        Logger::exception($error, ['type' => 'transaction_restore']);
                    }
                }
            }
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
