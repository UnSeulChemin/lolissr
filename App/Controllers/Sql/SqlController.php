<?php

declare(strict_types=1);

namespace App\Controllers\Sql;

use App\Controllers\Controller;
use App\Services\Sql\SqlReadService;

use Framework\Http\Request;

use Throwable;

final class SqlController extends Controller
{
    public function __construct(
        private readonly SqlReadService $sqlReadService,
        Request $request
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | PAGES
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->renderPage();
    }

    public function execute(): never
    {
        $sql = trim((string) $this->request->input('sql'));

        if ($sql === '')
        {
            $this->renderPage(error: 'Veuillez saisir une requête SQL.');
        }

        try
        {
            $this->renderPage(sql: $sql, result: $this->sqlReadService->execute($sql));
        }
        catch (Throwable $exception)
        {
            $this->renderPage(sql: $sql, error: $exception->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * @param list<object>|null $result
     */
    private function renderPage(string $sql = '', ?array $result = null, ?string $error = null): never
    {
        $this->title = 'SQL';

        $this->render('pages/sql/index', ['sql' => $sql, 'result' => $result, 'error' => $error]);
    }
}
