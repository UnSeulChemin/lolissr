<?php

namespace App\Core;

use PDO;
use PDOException;

class Database extends PDO
{
    private static ?self $instance = null;

    private function __construct()
    {
        $dsn = 'mysql:host=' . Functions::dbHost()
            . ';dbname=' . Functions::dbName()
            . ';charset=' . Functions::dbCharset();

        try
        {
            parent::__construct(
                $dsn,
                Functions::dbUser(),
                Functions::dbPass()
            );

            $this->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_OBJ
            );

            $this->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        }
        catch (PDOException $exception)
        {
            Logger::error($exception->getMessage());

            if (Functions::appDebug())
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

    private function __clone(): void {}
    private function __wakeup(): void {}
}