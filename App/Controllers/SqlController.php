<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\Sql\SqlRepository;

use Framework\Http\Request;

use Throwable;

final class SqlController extends Controller
{
    public function __construct(
        private readonly SqlRepository $sqlRepository,
        Request $request,
    ) {
        parent::__construct($request);
    }

    private function renderPage(
        string $sql = '',
        array $result = [],
        ?string $error = null,
    ): never {

        $this->title = 'SQL';

        $this->render(
            'pages/sql/index',
            [
                'sql' => $sql,
                'result' => $result,
                'error' => $error,
            ],
        );
    }

    public function index(): never
    {
        $this->renderPage();
    }

    public function execute(): never
    {
        $sql = trim(
            (string) $this->request->input('sql'),
        );

        try
        {
            $result =
                $this->sqlRepository
                    ->executeQuery($sql);

            $this->renderPage(
                $sql,
                $result,
            );
        }
        catch (Throwable $exception)
        {
            $this->renderPage(
                $sql,
                [],
                $exception->getMessage(),
            );
        }
    }
}