<?php

declare(strict_types=1);

namespace App\Core;

use Exception;
use PDO;
use PDOException;

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

            $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        catch (PDOException $exception)
        {
            Logger::error('Erreur connexion BDD : ' . $exception->getMessage());

            if (Functions::appDebug())
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