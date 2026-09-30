<?php

declare(strict_types=1);

require dirname(__DIR__) . '/phpstan-bootstrap.php';

use Framework\Config\Config;
use Framework\Cache\Cache;
use Framework\Config\Env;
use Framework\Http\Request;
use Framework\Routing\Route;
use Framework\Routing\RouteCollection;
use Framework\Support\DateNormalizer;
use Framework\Support\Session;
use Framework\Validation\Validator;

$check = static function (bool $condition, string $message): void {
    if (! $condition) throw new RuntimeException($message);
};

foreach (["01/01/20\0 26", "01/01/2026\0", '31/02/2026', 'not a date'] as $date)
{
    $check((new Validator(['date' => $date]))->date('date')->fails(), 'Invalid date accepted.');
    $check(DateNormalizer::normalize($date) === null, 'Invalid date normalized.');
}
foreach (['29/02/2024', '2024-02-29'] as $date)
{
    $check((new Validator(['date' => $date]))->date('date')->passes(), 'Valid date rejected.');
}
$check(DateNormalizer::normalize(' 29/02/2024 ') === '2024-02-29', 'Date normalization changed.');

foreach ([false, true] as $trustProxy)
{
    Env::set('TRUST_PROXY', $trustProxy);
    Config::clear();
    foreach ([
        [['HTTPS' => 'on'], true],
        [['HTTPS' => 'off', 'SERVER_PORT' => 80], false],
        [['SERVER_PORT' => '443'], true],
        [['HTTP_X_FORWARDED_PROTO' => ' HTTPS, http'], $trustProxy],
        [['HTTP_X_FORWARDED_PROTO' => 'http, https'], false],
        [[], false],
    ] as [$server, $expected])
    {
        $check((new Request(server: $server))->isHttps() === $expected, 'HTTPS detection changed.');
    }
}
Env::set('TRUST_PROXY', false);
Config::clear();

$routes = new RouteCollection();
$dynamic = new Route('GET', '/items/{id}', static function (): void {});
$static = new Route('GET', '/items/new', static function (): void {});
$later = new Route('GET', '/{section}/{id}', static function (): void {});
$post = new Route('POST', '/items/new', static function (): void {});
foreach ([$dynamic, $post, $static, $later] as $route) $routes->add($route);
$check($routes->candidates('GET', '/items/new') === [$dynamic, $static], 'Route precedence changed.');
$check($routes->candidates('GET', '/items/42') === [$dynamic, $later], 'Dynamic routes lost.');
$check($routes->candidates('POST', '/items/new') === [$post], 'Method separation changed.');
$check($routes->allowedMethodsFor('/items/new') === ['GET', 'POST'], 'Allow methods changed.');
try
{
    $routes->add($static);
    throw new LogicException('Duplicate route accepted.');
}
catch (RuntimeException) {}

$integerRoute = new Route('GET', '/items/{id:int}', static function (): void {});
foreach (['0' => 0, '00042' => 42, (string) PHP_INT_MAX => PHP_INT_MAX] as $input => $expected)
{
    $check($integerRoute->castParameters(['id' => (string) $input])['id'] === $expected, 'Valid integer route changed.');
}
foreach ([(string) PHP_INT_MAX . '0', str_repeat('9', strlen((string) PHP_INT_MAX)), '000' . PHP_INT_MAX . '0'] as $input)
{
    try
    {
        $integerRoute->castParameters(['id' => $input]);
        throw new LogicException('Overflowing route parameter accepted.');
    }
    catch (\Framework\Exceptions\NotFoundException) {}
}

$directory = sys_get_temp_dir() . '/framework-test-' . bin2hex(random_bytes(8));
mkdir($directory);
$sessionDirectory = new ReflectionProperty(Session::class, 'directory');
$sessionDirectory->setValue(null, $directory);
$cacheDirectory = new ReflectionProperty(Cache::class, 'directory');
$cacheDirectory->setValue(null, $directory);
$fixture = $directory . '/image.png';
file_put_contents($fixture, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5XcAAAAASUVORK5CYII='));
try
{
    Env::set('CACHE_ENABLED', true);
    Config::clear();
    $calls = 0;
    $compute = static function () use (&$calls): mixed { $calls++; return null; };
    Cache::remember('nullable', 60, $compute);
    Cache::remember('nullable', 60, $compute);
    $check($calls === 1, 'Cached null recomputed.');
    Cache::forget('nullable');
    Cache::remember('nullable', 60, $compute);
    $check($calls === 2, 'Invalidation did not refresh cache.');
    Cache::remember('invalidated-during-compute', 60, static function (): string {
        Cache::forget('invalidated-during-compute');
        return 'stale';
    });
    $check(Cache::remember('invalidated-during-compute', 60, static fn (): string => 'fresh') === 'fresh', 'Stale computation published after invalidation.');

    $file = ['tmp_name' => $fixture, 'name' => 'image.png', 'error' => UPLOAD_ERR_OK];
    $check((new Validator([], ['image' => $file]))->imageMime('image', ['image/png'])->passes(), 'PNG MIME rejected.');
    $check((new Validator([], ['image' => $file]))->imageMime('image', ['image/jpeg'])->fails(), 'Wrong MIME accepted.');

    Session::start();
    Session::set('success', 'Saved');
    Session::set('nullable', null);
    $check(session_status() === PHP_SESSION_ACTIVE, 'Explicit session scope lost its lock.');
    Session::close();
    $check(Session::get('success') === 'Saved', 'Session value lost.');
    $check(session_status() === PHP_SESSION_NONE, 'Read retained the lock.');
    $check(Session::has('nullable'), 'Stored null treated as absent.');
    $check(Session::get('nullable', 'fallback') === null, 'Stored null replaced by fallback.');
    $check(Session::pull('success') === 'Saved', 'Flash message lost.');
    $check(session_status() === PHP_SESSION_NONE, 'Flash consumption retained the lock.');
    $check(Session::pull('success') === null, 'Flash message consumed twice.');
    Session::set('old', ['title' => 'Draft']);
    $check(session_status() === PHP_SESSION_NONE, 'Write retained the lock.');
    $check(Session::pull('old') === ['title' => 'Draft'], 'Form state not persisted.');
    Session::remove('nullable');
    $check(! Session::has('nullable'), 'Removal not persisted.');
    Session::set('one', 1);
    Session::set('two', 2);
    Session::forget(['one', 'two']);
    $check(! Session::has('one') && ! Session::has('two'), 'Forget not persisted.');

    $token = csrf_token();
    $check(strlen($token) === 64 && csrf_token() === $token, 'CSRF token changed between reads.');
    $check(session_status() === PHP_SESSION_NONE, 'CSRF access retained the lock.');
    Session::start();
    $check(($_SESSION['csrf_token'] ?? null) === $token, 'CSRF token not persisted.');
    $_SESSION['fresh'] = 'External update';
    Session::close();
    $check(Session::get('fresh') === 'External update', 'Session read used stale data.');
    Session::regenerate();
    $check(session_status() === PHP_SESSION_NONE && csrf_token() === $token, 'Session regeneration lost state or retained the lock.');
}
finally
{
    Session::destroy();
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_file($directory . '/.metadata.lock')) unlink($directory . '/.metadata.lock');
    rmdir($directory);
}

echo "PASS: dates, HTTPS/proxy, route precedence, MIME validation, cache invalidation, session persistence and lock release, CSRF.\n";
