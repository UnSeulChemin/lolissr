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
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
        catch (PDOException $exception)
        {
            Logger::exception(
                $exception,
                [
                    'type' => 'database_connection',
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

        $transactionActive = false;

        try
        {
            if (! $this->beginTransaction())
            {
                throw new RuntimeException(
                    'Impossible de démarrer la transaction.'
                );
            }

            $transactionActive = true;

            $result = $callback();

            if (! $this->commit())
            {
                throw new RuntimeException(
                    'Impossible de valider la transaction.'
                );
            }

            $transactionActive = false;

            return $result;
        }
        catch (Throwable $exception)
        {
            if ($transactionActive)
            {
                $this->rollBack();
            }

            throw $exception;
        }
        finally
        {
            Profiler::end('database.transaction');
        }
    }
}