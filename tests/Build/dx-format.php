<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/scripts/Support/DxFormatter.php';

$root = dirname(__DIR__, 2);
$formatter = new DxFormatter($root, $root . '/dx.json');
$check = static function (bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
};

$php = <<<'PHP'
<?php
namespace Example;
use Framework\Http\Request;
use App\Models\User;
use App\Cache\DashboardCache;
final class Example
{
    public function value(
        int $id,
    ): array {
        return [
            'id' => $id,
        ];
    }
}
PHP;
$formatted = $formatter->format($php, 'php');
$check(str_contains($formatted, "use App\\Cache\\DashboardCache;\nuse App\\Models\\User;\n\nuse Framework\\Http\\Request;"), 'PHP imports are not grouped and sorted.');
$check(str_contains($formatted, "public function value(int \$id): array\n    {"), 'Compact PHP signature or Allman brace missing.');
$check(str_contains($formatted, "return ['id' => \$id];"), 'Short PHP array is not compact.');
$check($formatter->format($formatted, 'php') === $formatted, 'PHP formatter is not idempotent.');
$section = "<?php\n// =====\n// création\n// =====\n";
$check(str_contains($formatter->format($section, 'php'), '// CRÉATION'), 'Section heading is not uppercase.');

$literals = <<<'PHP'
<?php
$text = <<<'TEXT'
use Framework\Http\Request;
use App\Models\User;


literal(
    data,
)
TEXT;
$callback = function () use ($text) { return $text; };
PHP;
$literalResult = $formatter->format($literals, 'php');
preg_match("/<<<'TEXT'[\s\S]*?\nTEXT;/", $literals, $before);
preg_match("/<<<'TEXT'[\s\S]*?\nTEXT;/", $literalResult, $after);
$check($before[0] === $after[0], 'PHP nowdoc contents changed.');
$lineSensitive = "<?php\n\n\nreturn __LINE__;\n";
$check($formatter->format($lineSensitive, 'php') === $lineSensitive, 'Physical line-dependent source changed.');
$crlf = str_replace("\n", "\r\n", $php);
$check(!preg_match('/(?<!\r)\n/', $formatter->format($crlf, 'php')), 'CRLF line endings changed.');
$view = "<?php\n\$items = [\n    1,\n];\n?>\n<div>\n\n\nunchanged HTML</div>\n";
$check(str_ends_with($formatter->format($view, 'php'), "?>\n<div>\n\n\nunchanged HTML</div>\n"), 'View HTML changed.');

$js = <<<'JS'
const text = `literal(
    data,
)`;
const regex = /[(){}]/g;
const sparse = [,,];
const result = invoke(
    text,
    regex,
    sparse,
);
if (result) {
    console.log(result);
}
JS;
$jsResult = $formatter->format($js, 'js');
$check(str_contains($jsResult, 'invoke(text, regex, sparse)'), 'Short JavaScript call is not compact.');
$check(str_contains($jsResult, "if (result)\n{"), 'JavaScript Allman brace missing.');
$check(str_contains($jsResult, 'const sparse = [,,];'), 'Sparse array length changed.');
$check(str_contains($jsResult, '/[(){}]/g'), 'JavaScript regular expression changed.');
$check($formatter->format($jsResult, 'js') === $jsResult, 'JavaScript formatter is not idempotent.');
$imports = "import {\n    item,\n} from './module.js';\nexport function run(options = {}) {\n    return item(options);\n}\n";
$importResult = $formatter->format($imports, 'js');
$check(str_contains($importResult, "import { item } from './module.js';"), 'JavaScript import is not compact.');
$check($formatter->format($importResult, 'js') === $importResult, 'JavaScript import formatting is not idempotent.');
$css = ".example\n{\n    transition:\n        color .2s ease,\n        opacity .2s ease;\n    content: ' keep  spaces ';\n}\n";
$cssResult = $formatter->format($css, 'css');
$check(str_contains($cssResult, 'transition: color .2s ease, opacity .2s ease;'), 'CSS declaration is not compact.');
$check(str_contains($cssResult, "content: ' keep  spaces ';"), 'CSS string changed.');
$check($formatter->format($cssResult, 'css') === $cssResult, 'CSS formatter is not idempotent.');

// Exercise --check and path validation in an isolated workspace.
$directory = sys_get_temp_dir() . '/lolissr-dx-test-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
try
{
    $config = ['paths' => ['source.php'], 'extensions' => ['php'], 'exclude' => [], 'line_length' => 120];
    file_put_contents($directory . '/dx.json', json_encode($config, JSON_THROW_ON_ERROR));
    file_put_contents($directory . '/source.php', $php);
    $isolated = new DxFormatter($directory, $directory . '/dx.json');
    ob_start();
    try
    {
        $check($isolated->run(true) === 1, '--check did not report pending changes.');
        $check(file_get_contents($directory . '/source.php') === $php, '--check wrote to a file.');
        $check($isolated->run(false) === 0, 'DX apply failed.');
        $check(file_get_contents($directory . '/source.php') === $formatted, 'DX apply output differs.');
        $check($isolated->run(true) === 0, 'Second DX check is not clean.');
    }
    finally
    {
        ob_end_clean();
    }
    // An invalid later file must prevent writes to previously validated files.
    file_put_contents($directory . '/source.php', $php);
    file_put_contents($directory . '/broken.php', '<?php function broken(');
    $config['paths'] = ['source.php', 'broken.php'];
    file_put_contents($directory . '/dx.json', json_encode($config, JSON_THROW_ON_ERROR));
    ob_start();
    try
    {
        try
        {
            (new DxFormatter($directory, $directory . '/dx.json'))->run(false);
            throw new RuntimeException('DX accepted invalid PHP.');
        }
        catch (ParseError)
        {
            $check(file_get_contents($directory . '/source.php') === $php, 'DX wrote before validation completed.');
        }
    }
    finally
    {
        ob_end_clean();
    }
    $config['paths'] = ['../outside.php'];
    file_put_contents($directory . '/dx.json', json_encode($config, JSON_THROW_ON_ERROR));
    ob_start();
    try
    {
        try
        {
            (new DxFormatter($directory, $directory . '/dx.json'))->run(false);
            throw new RuntimeException('DX accepted a path outside the project.');
        }
        catch (InvalidArgumentException)
        {
        }
    }
    finally
    {
        ob_end_clean();
    }
}
finally
{
    foreach (['source.php', 'broken.php', 'dx.json'] as $name) if (is_file($directory . '/' . $name)) unlink($directory . '/' . $name);
    rmdir($directory);
}

echo "PASS: DX imports, compact expressions, Allman braces, literals, templates, sparse arrays, CRLF, idempotence, check/apply and project boundaries.\n";
