<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';

use Framework\Application\Bootstrap;
use Framework\Database\Database;

Bootstrap::loadEnvOnly();
$database = new Database();
$check = static function (bool $ok, string $message): void
{
    if (! $ok) throw new RuntimeException($message);
};
$reject = static function (string $sql, int $errorCode) use ($database): void
{
    try
    {
        $database->exec($sql);
    }
    catch (PDOException $error)
    {
        if ((int) ($error->errorInfo[1] ?? 0) === $errorCode) return;
        throw $error;
    }
    throw new RuntimeException('Invalid data accepted: ' . $sql);
};

// Copier le vrai schema dans des tables locales a cette connexion.
// CREATE TABLE LIKE ne copie pas les FK : verifier celle-ci par les metadonnees.
$foreignKey = $database->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'achievement_xp_rewards'
    AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users' AND REFERENCED_COLUMN_NAME = 'id'")->fetchColumn();
$check((int) $foreignKey === 1, 'Achievement user foreign key is missing');
foreach (['manga', 'users'] as $table)
{
    $database->exec("CREATE TEMPORARY TABLE schema_$table LIKE $table");
}
$database->exec("INSERT INTO schema_manga (user_id, thumbnail, extension, slug, livre, numero, statut)
    VALUES (1, 'fixture', 'webp', 'schema-fixture', 'Fixture', 1, 'en_cours')");
$check((int) $database->query('SELECT COUNT(*) FROM schema_manga
    WHERE editeur IS NULL AND jacquette IS NULL AND livre_note IS NULL AND note IS NULL')->fetchColumn() === 1,
    'Missing publisher/ratings must be stored as NULL');
$database->exec('UPDATE schema_manga SET jacquette = 4, livre_note = 5, note = 9');
$reject('UPDATE schema_manga SET note = 8', 3819);
$reject('UPDATE schema_manga SET jacquette = 6, note = 11', 3819);
$reject('UPDATE schema_manga SET lu = 2', 3819);
$reject('UPDATE schema_manga SET numero = 0', 3819);
$reject("UPDATE schema_manga SET statut = 'unknown'", 3819);
$reject("INSERT INTO schema_manga (user_id, thumbnail, extension, slug, livre, numero, statut)
    VALUES (1, 'other', 'webp', 'schema-fixture', 'Other', 1, 'en_cours')", 1062);
$database->exec("INSERT INTO schema_manga (user_id, thumbnail, extension, slug, livre, numero, statut)
    VALUES (2, 'other', 'webp', 'schema-fixture', 'Other', 1, 'en_cours')");
$database->exec('UPDATE schema_manga SET jacquette = NULL, note = NULL');
$database->exec("INSERT INTO schema_users (username, password) VALUES ('schema-fixture', 'fixture')");
$user = $database->query('SELECT avatar_extension, banner_extension, frame_extension FROM schema_users')->fetch();
$check($user->avatar_extension === 'webp' && $user->banner_extension === 'webp' && $user->frame_extension === 'webp',
    'Profile extension defaults differ from application defaults');
$reject('UPDATE schema_users SET level = 0', 3819);
$columns = $database->query("SELECT TABLE_NAME, COLUMN_TYPE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id'
    AND TABLE_NAME IN ('manga','artbook','figurine','nendoroid','peluche','chinois_grammaire','chinois_vocabulaire','users')")->fetchAll();
$check(count($columns) === 8, 'Expected identity columns are missing');
foreach ($columns as $column) $check($column->COLUMN_TYPE === 'int unsigned', 'Identity type differs: ' . $column->TABLE_NAME);
// A newly acquired unread volume may have a zero flag despite existing history.
// History is persistent; flags transfer only when the series is fully read again.
// Check the history constraints without imposing an invalid invariant on live data.
$database->exec('CREATE TEMPORARY TABLE schema_series_rewards LIKE manga_series_rewards');
$database->exec("INSERT INTO schema_series_rewards (user_id, slug) VALUES (1, 'schema-fixture'), (2, 'schema-fixture')");
$check((int) $database->query('SELECT COUNT(*) FROM schema_series_rewards')->fetchColumn() === 2,
    'Series history must remain isolated by owner');
$reject("INSERT INTO schema_series_rewards (user_id, slug) VALUES (1, 'schema-fixture')", 1062);
echo "PASS: actual MySQL schema accepts optional manga fields, rejects invalid ratings/flags, preserves uniqueness and aligns identities/profile defaults/history. Temporary fixtures only.\n";
