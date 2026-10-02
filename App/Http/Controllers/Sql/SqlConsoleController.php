<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sql;

use App\Http\Controllers\Controller;
use App\Services\Sql\SqlExecutionService;

use Framework\Http\Request;

use Throwable;

final class SqlConsoleController extends Controller
{
    public function __construct(
        private readonly SqlExecutionService $sqlExecutionService,
        Request $request
    ) {
        parent::__construct($request);
    }

    // --------------------------------------------------------------------------
    // PAGES
    // --------------------------------------------------------------------------

    public function index(): never
    {
        $this->renderPage();
    }

    public function execute(): never
    {
        $sql = trim($this->stringInput('sql'));

        if ($sql === '')
        {
            $this->renderPage(error: 'Veuillez saisir une requête SQL.');
        }

        try
        {
            $data = $this->sqlExecutionService->execute($sql);
            $this->renderPage(sql: $sql, result: $data['result'], truncated: $data['truncated']);
        }
        catch (Throwable $exception)
        {
            $this->renderPage(sql: $sql, error: $exception->getMessage());
        }
    }

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

    /**
     * @param list<object>|null $result
     */
    private function renderPage(string $sql = '', ?array $result = null, ?string $error = null, bool $truncated = false): never
    {
        $this->title = 'SQL';

        $this->render('pages/sql/index', ['sql' => $sql, 'result' => $result, 'error' => $error, 'truncated' => $truncated]);
    }
}
