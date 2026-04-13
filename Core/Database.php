<?php

namespace App\Core;

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

            /**
             * Définit le mode de récupération par défaut.
             * Les résultats seront retournés en objet.
             */
            $this->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_OBJ
            );

            /**
             * Active le mode exception pour les erreurs PDO.
             */
            $this->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        }

        catch (PDOException $exception)
        {
            /**
             * Enregistre l'erreur dans le logger.
             */
            Logger::error($exception->getMessage());

            /**
             * Affiche l'erreur complète en mode debug.
             */
            if (Functions::appDebug())
            {
                exit('Erreur PDO : ' . $exception->getMessage());
            }

            /**
             * Message générique en production.
             */
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
    private function __clone(): void {}

    /**
     * Empêche la désérialisation de l'instance.
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}