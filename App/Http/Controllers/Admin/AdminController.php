<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\Admin\MaintenanceJob;
use App\Services\Admin\RecommendationJob;
use App\Services\Admin\ReleaseJob;

use RuntimeException;
final class AdminController extends Controller
{
    private function commandError(string $path, string $message, bool $withOld = false): never
    {
        if ($this->expectsJson()) \Framework\Http\Responses\Response::json(['success' => false, 'message' => $message], 409);
        $this->redirectWithError($path, $message, $withOld);
    }
    private function commandSuccess(string $path, string $message): never
    {
        if ($this->expectsJson()) \Framework\Http\Responses\Response::json(['success' => true, 'message' => $message]);
        $this->redirectWithSuccess($path, $message);
    }
    public function index(): never
    {
        $this->title = 'Administration';
        $this->render('pages/admin/index', ['sqlEnabled' => env_bool('SQL_TOOL_ENABLED', true)]);
    }
    public function commands(): never
    {
        $this->title = 'Commandes';
        $this->render('pages/admin/commands', ['job' => RecommendationJob::status(), 'output' => RecommendationJob::output(), 'releaseJob' => ReleaseJob::status(), 'releaseOutput' => ReleaseJob::output()]);
    }
    public function jobStatus(): never
    {
        header('Cache-Control: no-store');
        \Framework\Http\Responses\Response::json(['success' => true, 'jobs' => [
            'recommendations' => ['status' => RecommendationJob::status(), 'output' => RecommendationJob::output()],
            'releases' => ['status' => ReleaseJob::status(), 'output' => ReleaseJob::output()],
            'maintenance' => ['status' => MaintenanceJob::status(), 'output' => MaintenanceJob::output()]
        ]]);
    }
    public function dev(): never
    {
        $this->title = 'Dev';
        $this->devCommands();
    }
    public function devCommands(): never
    {
        $this->title = 'Commandes Dev';
        $this->render('pages/admin/dev-commands', ['job' => MaintenanceJob::status(), 'output' => MaintenanceJob::output()]);
    }
    public function images(): never
    { $this->maintenance('images'); }
    public function clearCache(): never
    { $this->maintenance('cache'); }
    private function maintenance(string $task): never
    {
        try
        { MaintenanceJob::start($task); }
        catch (RuntimeException $exception)
        { $this->commandError('admin/dev', $exception->getMessage(), false); }
        $this->commandSuccess('admin/dev', 'Maintenance lancée en arrière-plan.');
    }
    public function releases(): never
    { $this->startReleases(null); }
    public function myReleases(): never
    {
        $owner = user();
        if ($owner === null) throw new \Framework\Http\Exceptions\NotFoundException();
        $this->startReleases($owner->id);
    }
    private function startReleases(?int $ownerId): never
    {
        try
        { ReleaseJob::start($ownerId); }
        catch (RuntimeException $exception)
        { $this->commandError('admin/commandes', $exception->getMessage(), false); }
        $this->commandSuccess('admin/commandes', 'Actualisation des sorties lancée en arrière-plan.');
    }
    public function myRecommendations(): never
    {
        $owner = user();
        if ($owner === null) throw new \Framework\Http\Exceptions\NotFoundException();
        try
        { RecommendationJob::start($owner->id); }
        catch (RuntimeException $exception)
        { $this->commandError('admin/commandes', $exception->getMessage(), false); }
        $this->commandSuccess('admin/commandes', 'Actualisation de votre compte lancée en arrière-plan.');
    }
    public function recommendations(): never
    {
        try
        { RecommendationJob::start(); }
        catch (RuntimeException $exception)
        { $this->commandError('admin/commandes', $exception->getMessage(), false); }
        $this->commandSuccess('admin/commandes', 'Actualisation lancée en arrière-plan.');
    }
}
