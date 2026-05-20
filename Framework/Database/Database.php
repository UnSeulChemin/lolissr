<?php

declare(strict_types=1);

namespace Framework\Database;

use Framework\Application\App;
use Framework\Config\DatabaseConfig;
use Framework\Support\Logger;
use PDO;
use PDOException;
use RuntimeException;

final class Database extends PDO
{
    public function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DatabaseConfig::host(),
            DatabaseConfig::name(),
            DatabaseConfig::charset(),
        );

        try {
            parent::__construct(
                $dsn,
                DatabaseConfig::user(),
                DatabaseConfig::pass(),
            );

            $this->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_OBJ,
            );

            $this->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION,
            );

            $this->setAttribute(
                PDO::ATTR_EMULATE_PREPARES,
                false,
            );
        } catch (PDOException $exception) {
            Logger::exception(
                $exception,
                [
                    'type' => 'database_connection',
                ],
            );

            throw new RuntimeException(
                App::debug()
                    ? $exception->getMessage()
                    : 'Erreur de connexion à la base de données.',
            );
        }
    }
}
