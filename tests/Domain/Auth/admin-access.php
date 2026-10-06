<?php
declare(strict_types=1);
require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';
use App\Http\Middleware\AdminOwnerMiddleware;
use App\Models\User\User;
use App\Services\Auth\AuthService;

use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Requests\Request;
$reflection = new ReflectionClass(AuthService::class);
$authentication = $reflection->newInstanceWithoutConstructor();
$reflection->getProperty('userResolved')->setValue($authentication, true);
$middleware = new AdminOwnerMiddleware($authentication);
foreach ([null, 1, 2] as $id)
{
    $user = $id === null ? null : new User();
    if ($user !== null)
    { $user->id = $id; $user->is_admin = $id === 2; }
    $reflection->getProperty('currentUser')->setValue($authentication, $user);
    $allowed = true;
    try
    { $middleware->handle(new Request()); }
    catch (NotFoundException)
    { $allowed = false; }
    if ($allowed !== ($id === 1)) throw new RuntimeException('Admin access must be restricted to account 1, including other admins.');
}
echo "PASS: account 1 allowed; guests and other administrators denied.\n";
