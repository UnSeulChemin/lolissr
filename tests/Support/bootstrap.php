<?php

declare(strict_types=1);

if (! defined('ROOT'))
{
    define('ROOT', dirname(__DIR__, 2));
}

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';

// Les tests de domaine utilisent un compte explicite, sans session HTTP reelle.
// Les tests XP qui definissent deja user() conservent leur propre fournisseur.
if (! function_exists('user'))
{
    function user(): ?\App\Models\User\User
    {
        if (array_key_exists('testCurrentUser', $GLOBALS)) return $GLOBALS['testCurrentUser'];
        $user = new \App\Models\User\User();
        $user->id = 1;
        return $user;
    }
}

// Adapter les schemas historiques des fixtures, jamais le schema de production.
// Les INSERT sans liste de colonnes gardent leurs anciennes valeurs positionnelles.
function owned_fixture_sql(PDO $database, string $sql): string
{
    $tables = ['manga', 'artbook', 'figurine', 'nendoroid', 'peluche', 'chinois_grammaire', 'chinois_vocabulaire', 'manga_series_rewards'];
    if (preg_match('/^CREATE\s+(?:TEMPORARY\s+)?TABLE\s+([a-z_]+)\s*\(/i', trim($sql), $match)
        && in_array($match[1], $tables, true) && ! str_contains($sql, 'user_id'))
    {
        return preg_replace('/\(/', '(user_id INT NOT NULL DEFAULT 1, ', $sql, 1);
    }
    if (preg_match('/^(INSERT INTO\s+([a-z_]+))\s+VALUES\b/i', trim($sql), $match) && in_array($match[2], $tables, true))
    {
        $table = $match[2];
        $sqlite = $database->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        $rows = $database->query($sqlite ? "PRAGMA table_info($table)" : "SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        $columns = array_column($rows, $sqlite ? 'name' : 'Field');
        $columns = array_values(array_diff($columns, ['user_id']));
        return preg_replace('/^INSERT INTO\s+[a-z_]+\s+VALUES\b/i', $match[1] . ' (' . implode(', ', $columns) . ') VALUES', trim($sql));
    }
    return $sql;
}
