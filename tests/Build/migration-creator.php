<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/scripts/Database/Support/MigrationCreator.php';
$directory = sys_get_temp_dir() . '/migration-creator-' . bin2hex(random_bytes(8));
try
{
    foreach (['', '../escape', 'ajout/table', 'UPPERCASE', 'bad name', str_repeat('a', 81)] as $invalid)
    {
        $rejected = false;
        try
        { MigrationCreator::create($directory, $invalid); }
        catch (InvalidArgumentException)
        { $rejected = true; }
        if (!$rejected || is_dir($directory)) throw new RuntimeException('Invalid name must be rejected before filesystem changes.');
    }
    $first = MigrationCreator::create($directory, 'ajout-table');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}-\d{6}-\d{6}-ajout-table\.sql$/', basename($first))
        || file_get_contents($first) !== '') throw new RuntimeException('Invalid filename/template.');
    file_put_contents($first, 'SELECT 1;');
    $second = MigrationCreator::create($directory, 'ajout-table');
    if ($first === $second || file_get_contents($first) !== 'SELECT 1;' || file_get_contents($second) !== '')
        throw new RuntimeException('Repeated creation must preserve earlier SQL.');
    echo "PASS: migration naming, empty template, directory creation, repeated creation and invalid path rejection.\n";
}
finally
{
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_dir($directory)) rmdir($directory);
}
