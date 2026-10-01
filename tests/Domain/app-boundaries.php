<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

use App\Controllers\Controller;
use Framework\Config\Config;
use Framework\Container\Container;
use Framework\Database\Database;
use Framework\Http\Request;

$check = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};
Config::prime(['app' => ['base_uri' => '/lolissr', 'pagination' => 8]]);
$resolve = static function (array $input, bool $post): string {
    $request = $post ? new Request(post: $input) : new Request(get: $input);
    $controller = new class($request) extends Controller {
        public function returnPath(): string { return $this->returnPathInput(); }
    };
    return $controller->returnPath();
};
foreach ([false, true] as $post)
{
    foreach (['', 'https://example.invalid/', '//example.invalid', '/outside', '../outside',
        'chinois/../../outside', '%2f%2fexample.invalid', 'javascript:alert(1)',
        'chinois\\outside', "chinois/flashcards\r\nLocation: https://example.invalid/",
        'chinois/%2e%2e/outside', ' https://example.invalid/'] as $value)
    {
        $check($resolve(['return_to' => $value], $post) === '', 'Unsafe return path accepted.');
    }
    foreach (['chinois/flashcards/vocabulaire', 'chinois/grammaire/hsk1#section-2',
        'chinois/vocabulaire/mandarin/page/2?q=bonjour%20monde#result'] as $value)
    {
        $check($resolve(['return_to' => $value], $post) === $value, 'Internal return path lost.');
    }
    $check($resolve([], $post) === '', 'Missing return path changed.');
    try
    {
        $resolve(['return_to' => ['invalid']], $post);
        throw new LogicException('Array return path accepted.');
    }
    catch (Framework\Http\Exceptions\ValidationException) {}
}

// Temporary in-memory tables only; never connect to the application database.
$database = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($database, 'sqlite::memory:');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
foreach (['manga', 'artbook', 'figurine', 'nendoroid', 'peluche', 'chinois_vocabulaire'] as $table)
    $database->exec("CREATE TABLE $table (id INT, slug TEXT, langue TEXT)");
$container = new Container();
$container->instance(Database::class, $database);
foreach ([
    [App\Services\Manga\MangaReadService::class, 'series'],
    [App\Services\Manga\ArtbookReadService::class, 'artbooks'],
    [App\Services\Figurine\FigurineReadService::class, 'waifus'],
    [App\Services\Nendoroid\NendoroidReadService::class, 'waifus'],
    [App\Services\Peluche\PelucheReadService::class, 'waifus'],
    [App\Services\Chinois\ChinoisReadService::class, 'langue'],
] as [$class, $method])
{
    $service = $container->get($class);
    foreach ([1, 2, 999, PHP_INT_MAX] as $page)
    {
        $result = $method === 'langue' ? $service->$method('mandarin', $page) : $service->$method($page);
        $check($page === 1 ? $result !== null && $result->currentPage === 1 && $result->totalPages === 1 : $result === null,
            'Empty collection pagination differs: ' . $class . ' page ' . $page);
    }
}
$service = $container->get(App\Services\Chinois\ChinoisReadService::class);
$buildSections = new ReflectionMethod($service, 'buildSections');
$grammar = [];
foreach (['École', 'Ecole', 'ecole-2', '!!!', 'section-2', '中文'] as $index => $section)
{
    $grammar[] = new App\DTO\Chinois\Responses\ChinoisGrammaireData(
        id: $index + 1, niveau: 'HSK1', section: $section, categorie: 'Fixture',
        titre: 'Title', structure: '', abreviation: null, phrase: '', pinyin: '',
        traduction: '', explication: '', position: $index, maitrise: false,
        xpRewarded: false, hasAbreviation: false, hasExplication: false,
        masteredClass: '', masteredValue: '0', masteredPressed: 'false', masteredLabel: ''
    );
}
$sections = $buildSections->invoke($service, $grammar);
$check(array_column($sections, 'id') === ['ecole', 'ecole-3', 'ecole-2', 'section-3', 'section-2', 'zhong-wen'],
    'Section transliteration, reserved slugs or collision suffixes changed');
$check($buildSections->invoke($service, []) === [], 'Empty grammar sections changed');
foreach ($sections as $index => $section)
    $check($section->categories[0]->grammaires[0] === $grammar[$index], 'Section grouping changed');
Config::clear();
echo "PASS: internal return paths on GET/POST and empty pagination across six collections.\n";
