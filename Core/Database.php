<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database extends PDO
{
    /**
     * Instance unique de la base de données.
     */
    private static ?self $instance = null;

    /**
     * Constructeur privé.
     * Initialise la connexion PDO.
     */
    private function __construct()
    {
        $host = (string) Config::get('database.host', 'localhost');
        $name = (string) Config::get('database.name', '');
        $charset = (string) Config::get('database.charset', 'utf8mb4');
        $user = (string) Config::get('database.user', '');
        $pass = (string) Config::get('database.pass', '');

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

            if ((bool) Config::get('app.debug', false))
            {
                exit('Erreur PDO : ' . $exception->getMessage());
            }

            exit('Erreur de connexion à la base de données.');
        }
    }

    /**
     * Retourne l'instance unique de la base de données.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Empêche le clonage de l'instance.
     */
    private function __clone(): void
    {
    }

    /**
     * Empêche la désérialisation de l'instance.
     */
    public function __wakeup(): void
    {
        throw new Exception('Cannot unserialize singleton');
    }
}