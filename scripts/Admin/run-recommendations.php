<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';
use App\Services\Admin\RecommendationJob;
$ownerId = isset($argv[1]) ? filter_var($argv[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;
if ($ownerId === false || count($argv) > 2) throw new InvalidArgumentException('Invalid account argument.');
$lock = fopen(RecommendationJob::directory() . '/recommendations.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX)) exit(1);
try
{
    if (RecommendationJob::status()['state'] !== 'queued') exit(1);
    RecommendationJob::writeState('running');
    $log = RecommendationJob::directory() . '/recommendations.log';
    $command = [PHP_BINARY, $root . '/scripts/Manga/sync-recommendations.php'];
    if ($ownerId !== null) $command[] = (string) $ownerId;
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']], $pipes, $root);
    if (!is_resource($process)) throw new RuntimeException('Cannot start recommendation sync.');
    fclose($pipes[0]);
    RecommendationJob::writeState(proc_close($process) === 0 ? 'done' : 'failed');
} catch (Throwable $exception)
{
    file_put_contents(RecommendationJob::directory() . '/recommendations.log', $exception->getMessage() . PHP_EOL, FILE_APPEND);
    RecommendationJob::writeState('failed');
} finally
{ flock($lock, LOCK_UN); fclose($lock); }
