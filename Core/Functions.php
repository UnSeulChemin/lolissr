<?php
namespace App\Core;

class Functions
{
    public static function basePath(): string
    {
        static $config = null;

        if ($config === null) {
            $config = require __DIR__ . '/../Config/config.php';
        }

        return $config['base_path'] ?? '/';
    }
}