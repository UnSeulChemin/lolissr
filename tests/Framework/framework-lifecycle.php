<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

use App\Controllers\Controller;
use Framework\Config\Env;
use Framework\Debug\Profiler;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Logging\Logger;
use Framework\Http\Session;

if (($argv[1] ?? '') === 'profiler-child')
{
    Env::set('APP_DEBUG', true);
    Env::set('PROFILER_ENABLED', true);
    Env::set('LOG_ENABLED', true);
    (new ReflectionProperty(Logger::class, 'directory'))->setValue(null, $argv[2]);
    $startedAt = hrtime(true);
    usleep(20_000);
    Profiler::startRequest($startedAt);
    register_shutdown_function(static fn () => Profiler::finishRequest('GET', '/fixture'));
    Profiler::measure('completed', static fn () => usleep(2000));
    Profiler::measure('router.dispatch', static function (): void {
        Profiler::measure('controller.action', static function (): void {
            usleep(2000);
            Response::json(['success' => true]);
        });
    });
    exit(1);
}

$check = static function (bool $condition, string $message): void {
    if (! $condition) throw new RuntimeException($message);
};
$directory = sys_get_temp_dir() . '/framework-lifecycle-' . bin2hex(random_bytes(8));
mkdir($directory);
(new ReflectionProperty(Session::class, 'directory'))->setValue(null, $directory);
// Observe every persisted session snapshot, including any intermediate writes.
$handler = new class extends SessionHandler {
    public array $writes = [];
    public function write(string $id, string $data): bool
    {
        $this->writes[] = $_SESSION;
        return parent::write($id, $data);
    }
};
session_set_save_handler($handler, true);
try
{
    Session::start();
    Session::set('success', 'Saved');
    Session::set('errors', ['title' => 'Required']);
    Session::set('old', ['title' => 'Draft']);
    Session::close();
    $handler->writes = [];
    $controller = new class(new Request()) extends Controller {
        public function form(): \App\DTO\Common\Responses\FormViewData
        {
            return $this->formViewData('/save', '/cancel');
        }
    };
    $form = $controller->form();
    $check($form->errors === ['title' => 'Required'] && $form->old === ['title' => 'Draft'], 'Form state split.');
    $check($form->toast->message === 'Saved', 'Toast lost.');
    $check(count($handler->writes) === 1, 'Form state persisted in multiple lock scopes.');
    $check(! array_key_exists('errors', $handler->writes[0]) && ! array_key_exists('old', $handler->writes[0]), 'Form state not consumed.');
    $check(session_status() === PHP_SESSION_NONE, 'Form retained the session lock.');

    $handler->writes = [];
    $hasFeedback = Session::withLock(static fn (): bool =>
        Session::has('success') || Session::has('error') || Session::has('errors') || Session::has('old'));
    $check(! $hasFeedback, 'Consumed feedback still present.');
    $check(count($handler->writes) === 1, 'Feedback lookup reopened the session multiple times.');
    $check(session_status() === PHP_SESSION_NONE, 'Feedback lookup retained the session lock.');

    Session::start();
    Session::withLock(static function () use ($check): void {
        Session::withLock(static fn () => Session::set('nested', true));
        $check(session_status() === PHP_SESSION_ACTIVE, 'Nested scope released outer lock.');
    });
    $check(session_status() === PHP_SESSION_ACTIVE, 'Existing lock not preserved.');
    Session::close();
    try
    {
        Session::withLock(static function (): void {
            Session::set('before-error', true);
            throw new LogicException('fixture');
        });
    }
    catch (LogicException) {}
    $check(session_status() === PHP_SESSION_NONE, 'Exception retained the lock.');
    $check(Session::get('before-error') === true, 'Exception lost persisted session state.');
    Session::destroy();

    $process = proc_open([PHP_BINARY, __FILE__, 'profiler-child', $directory], [
        0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
    ], $pipes);
    $check(is_resource($process), 'Could not start profiler fixture.');
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $check(proc_close($process) === 0 && $error === '', 'Profiler fixture failed: ' . $error);
    $check(json_decode($output, true) === ['success' => true], 'Response changed.');
    $logs = glob($directory . '/app-*.log') ?: [];
    $check(count($logs) === 1, 'Profiler log missing.');
    $record = json_decode(trim(file_get_contents($logs[0])), true, 512, JSON_THROW_ON_ERROR);
    $durations = $record['context']['durations_ms'];
    $check(($durations['bootstrap.configure'] ?? 0) >= 15, 'Bootstrap duration excluded.');
    $check($record['context']['total_ms'] >= $durations['bootstrap.configure'], 'Bootstrap excluded from total.');
    foreach (['completed', 'router.dispatch', 'controller.action'] as $name)
    {
        $check(($durations[$name] ?? 0) > 0, 'Missing profiler duration: ' . $name);
    }
}
finally
{
    if (session_status() === PHP_SESSION_ACTIVE) Session::destroy();
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_file($directory . '/.cleanup.lock')) unlink($directory . '/.cleanup.lock');
    rmdir($directory);
}

echo "PASS: atomic form state, nested/exception session scopes and profiler durations after Response::json exit.\n";
