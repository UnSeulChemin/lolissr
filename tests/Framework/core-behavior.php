<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Cache\Cache;
use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Http\Request;
use Framework\Http\Session;
use Framework\Routing\Route;
use Framework\Routing\RouteCollection;
use Framework\Support\DateNormalizer;
use Framework\Validation\Validator;

$check = static function (bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
};

foreach ([true, false, 1.0, 1.5, [], new stdClass(), INF, NAN] as $value)
{
    $check((new Validator(['number' => $value]))->integer('number')->fails(), 'Non-integer accepted.');
}
foreach ([0, '0', -1, '-1', PHP_INT_MAX, (string) PHP_INT_MAX, ' 12 '] as $value)
{
    $check(! (new Validator(['number' => $value]))->integer('number')->fails(), 'Valid integer rejected.');
}
foreach ([NAN, INF, -INF, true, false, '1e999', [], new stdClass()] as $value)
{
    foreach (['numeric', 'min', 'max'] as $rule)
    {
        $validator = new Validator(['number' => $value]);
        $rule === 'numeric' ? $validator->numeric('number') : $validator->$rule('number', 0);
        $check($validator->fails(), 'Invalid number accepted by ' . $rule);
    }
}
foreach ([0, 0.0, -1.5, '0', ' 12.5 ', '1e2'] as $value)
{
    $check(! (new Validator(['number' => $value]))->numeric('number')->fails(), 'Valid number rejected.');
}

// Missing values must not cache the first caller's fallback; stored null stays null.
$items = new ReflectionProperty(Config::class, 'items');
$items->setValue(null, ['fixture' => ['null' => null, 'false' => false, 'zero' => 0, 'nested' => ['value' => 'ok']]]);
foreach (['first', 'second'] as $fallback)
{
    $check(Config::get('fixture.missing', $fallback) === $fallback, 'Configuration fallback cached.');
    $check(Config::get('fixture.null', $fallback) === null, 'Configured null replaced by fallback.');
    $check(Config::get('fixture.false', $fallback) === false, 'Configured false lost.');
    $check(Config::get('fixture.zero', $fallback) === 0, 'Configured zero lost.');
    $check(Config::get('fixture.zero.child', $fallback) === $fallback, 'Scalar traversed as array.');
    $check(Config::get(' .fixture..nested.value. ') === 'ok', 'Configuration key normalization changed.');
    $check(Config::get(' .. ', $fallback) === $fallback, 'Empty configuration key changed.');
}
$check(Config::get('fixture') === $items->getValue()['fixture'], 'Whole configuration file changed.');
foreach (['/first', '/second', '/'] as $baseUri)
{
    Env::set('APP_BASE_URI', $baseUri);
    Config::clear();
    $check(base_uri() === rtrim($baseUri, '/'), 'Base URI ignored configuration reload.');
    $check(view_base_uri() === rtrim($baseUri, '/') . '/', 'View base URI ignored reload.');
}

$container = new \Framework\Container\Container();
$container->singleton('cycle', static fn ($container) => $container->get('cycle'));
try
{
    $container->get('cycle');
    throw new RuntimeException('Circular dependency accepted.');
}
catch (\Framework\Container\ContainerResolutionException $exception)
{
    $check(str_contains($exception->getMessage(), 'cycle -> cycle'), 'Resolution chain lost.');
}
$container->singleton('cycle', static fn () => new stdClass());
$check($container->get('cycle') === $container->get('cycle'), 'Singleton or resolution recovery broken.');
$check($container->get(\Framework\Container\Container::class) === $container, 'Container self-resolution changed.');

foreach (["01/01/20\0 26", "01/01/2026\0", '31/02/2026', 'not a date'] as $date)
{
    $check((new Validator(['date' => $date]))->date('date')->fails(), 'Invalid date accepted.');
    $check(DateNormalizer::normalize($date) === null, 'Invalid date normalized.');
}
foreach (['29/02/2024', '2024-02-29'] as $date)
{
    $check(! (new Validator(['date' => $date]))->date('date')->fails(), 'Valid date rejected.');
}
$check(DateNormalizer::normalize(' 29/02/2024 ') === '2024-02-29', 'Date normalization changed.');

$base = ['waifu' => 'Test', 'origin' => 'Test', 'scale' => '1/7', 'company' => 'Test'];
$form = static fn (array $extra) => new \App\Http\Requests\Figurine\FigurineUpdateRequest(new Request(post: $base + $extra));
foreach (['commentaire', 'release_date', 'height_cm'] as $field)
{
    foreach ([[], ['bad']] as $invalid)
    {
        $check($form([$field => $invalid])->fails(), 'Array accepted for nullable field: ' . $field);
    }
    foreach ([null, '', '   ', "\t\r\n"] as $empty)
    {
        $request = $form([$field => $empty]);
        $check(! $request->fails(), 'Empty nullable field rejected: ' . $field);
        $check($request->dto()->$field === null, 'Empty nullable field was not normalized: ' . $field);
    }
}
foreach (['2026-10-01', '01/10/2026', ' 2026-10-01 '] as $date)
{
    $request = $form(['release_date' => $date]);
    $check(! $request->fails() && $request->dto()->release_date === '2026-10-01', 'Valid date lost in DTO.');
}
foreach (['2026-02-29', '31/04/2026', "\0"] as $date)
{
    $check($form(['release_date' => $date])->fails(), 'Invalid nullable date accepted.');
}
foreach ([0, '0', '0.0', ' 12.5 '] as $number)
{
    $request = $form(['height_cm' => $number]);
    $check(! $request->fails() && $request->dto()->height_cm === (float) $number, 'Numeric value lost during normalization.');
}

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
        [[], false]
    ] as [$server, $expected])
    {
        $check((new Request(server: $server))->isHttps() === $expected, 'HTTPS detection changed.');
    }
}
Env::set('TRUST_PROXY', false);
Config::clear();

$routes = new RouteCollection();
$dynamic = new Route('GET', '/items/{id}', static function (): void
{});
$static = new Route('GET', '/items/new', static function (): void
{});
$later = new Route('GET', '/{section}/{id}', static function (): void
{});
$post = new Route('POST', '/items/new', static function (): void
{});
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
catch (RuntimeException)
{}

$integerRoute = new Route('GET', '/items/{id:int}', static function (): void
{});
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
    catch (\Framework\Http\Exceptions\NotFoundException)
    {}
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
    $compute = static function () use (&$calls): mixed
    { $calls++; return null; };
    Cache::remember('nullable', 60, $compute);
    Cache::remember('nullable', 60, $compute);
    $check($calls === 1, 'Cached null recomputed.');
    Cache::forget('nullable');
    Cache::remember('nullable', 60, $compute);
    $check($calls === 2, 'Invalidation did not refresh cache.');
    Cache::remember('invalidated-during-compute', 60, static function (): string
    {
        Cache::forget('invalidated-during-compute');
        return 'stale';
    });
    $check(Cache::remember('invalidated-during-compute', 60, static fn (): string => 'fresh') === 'fresh', 'Stale computation published after invalidation.');

    $file = ['tmp_name' => $fixture, 'name' => 'image.png', 'error' => UPLOAD_ERR_OK];
    $check(! (new Validator([], ['image' => $file]))->imageMime('image', ['image/png'])->fails(), 'PNG MIME rejected.');
    $check((new Validator([], ['image' => $file]))->imageMime('image', ['image/jpeg'])->fails(), 'Wrong MIME accepted.');

    Session::start();
    Session::set('native-close', 'persisted');
    session_write_close();
    Session::set('after-native-close', 'also persisted');
    Session::close();
    $check(Session::get('after-native-close') === 'also persisted', 'Native session close lost subsequent writes.');
    ini_set('session.use_strict_mode', '0');
    ini_set('session.cookie_httponly', '0');
    $check(Session::get('after-native-close') === 'also persisted', 'Session reopen lost state.');
    $check(ini_get('session.use_strict_mode') === '1' && ini_get('session.cookie_httponly') === '1', 'Reopen did not restore secure options.');
    $_SERVER['HTTPS'] = 'on';
    Session::get('after-native-close');
    $check(session_get_cookie_params()['secure'], 'HTTPS change did not reconfigure session.');
    unset($_SERVER['HTTPS']);
    Session::start();
    Session::set('aborted', true);
    session_abort();
    $check(Session::get('aborted') === null, 'Native session abort left stale in-memory state.');
    $check(Session::get('native-close') === 'persisted', 'Native session close lost saved state.');
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

echo "PASS: numeric validation, configuration reload/defaults, container recovery, dates, HTTPS/proxy, routes, MIME, cache, native/framework session lifecycle and CSRF.\n";
