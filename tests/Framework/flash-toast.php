<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

use App\Controllers\Controller;
use Framework\Http\Request;

session_save_path(sys_get_temp_dir());
session_id('toast-test-' . bin2hex(random_bytes(8)));
session_start();
try
{
    foreach (['success', 'error'] as $key)
    {
        $_SESSION = [$key => 'Pending message'];
        $make = static fn (bool $prefetch) => new class($prefetch) extends Controller {
            public function __construct(bool $prefetch) { $this->request = new Request(server: ['HTTP_X_PREFETCH' => $prefetch ? 'true' : 'false']); }
            public function toast(): \App\DTO\Common\Responses\FlashToastData { return $this->flashToastData(); }
            public function form(): \App\DTO\Common\Responses\FormViewData { $this->baseUri = ""; return $this->formViewData('/save', '/cancel'); }
        };
        if ($make(true)->toast()->message !== null || ($_SESSION[$key] ?? null) !== 'Pending message')
            throw new RuntimeException('Prefetch consumed or exposed a flash message.');
        $real = $make(false);
        if ($real->toast()->message !== 'Pending message' || isset($_SESSION[$key]))
            throw new RuntimeException('Navigation did not consume the message.');
        if ($real->toast()->message !== 'Pending message' || $make(false)->toast()->message !== null)
            throw new RuntimeException('Message lifetime is incorrect.');
    }
    $_SESSION = ['errors' => ['title' => 'Required'], 'old' => ['title' => 'Draft']];
    $snapshot = $_SESSION;
    $speculative = $make(true)->form();
    if ($_SESSION !== $snapshot || $speculative->errors !== [] || $speculative->old !== [])
        throw new RuntimeException('Prefetch consumed or exposed form state.');
    $actual = $make(false)->form();
    if ($actual->errors !== $snapshot['errors'] || $actual->old !== $snapshot['old'] || isset($_SESSION['errors']) || isset($_SESSION['old']))
        throw new RuntimeException('Real form did not receive and consume validation state.');
    if ($make(false)->form()->old !== []) throw new RuntimeException('Form state replayed.');
}
finally { session_destroy(); }
echo "PASS: prefetch preserves success/error; navigation consumes once.\n";
