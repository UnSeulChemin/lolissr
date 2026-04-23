<?php

declare(strict_types=1);

namespace App\Core\Database;

use App\Core\Config\DatabaseConfig;
use App\Core\Support\Logger;
use Exception;
use PDO;
use PDOException;

class Database extends PDO
{
    private static ?self $instance = null;

    private function __construct()
    {
        $host = DatabaseConfig::host();
        $name = DatabaseConfig::name();
        $charset = DatabaseConfig::charset();
        $user = DatabaseConfig::user();
        $pass = DatabaseConfig::pass();

        $dsn = 'mysql:host=' . $host
            . ';dbname=' . $name
            . ';charset=' . $charset;

        try
        {
            parent::__construct($dsn, $user, $pass);

            $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        catch (PDOException $exception)
        {
            Logger::error('Erreur connexion BDD : ' . $exception->getMessage());

            if (\config('app.debug', false))
            {
                exit('Erreur PDO : ' . $exception->getMessage());
            }

            exit('Erreur de connexion à la base de données.');
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __clone(): void
    {
    }

    public function __wakeup(): void
    {
        throw new Exception('Cannot unserialize singleton');
    }
}