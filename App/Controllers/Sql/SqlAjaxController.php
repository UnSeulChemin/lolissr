<?php

declare(strict_types=1);

namespace App\Controllers\Sql;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Services\Sql\SqlReadService;

use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

use Throwable;

final class SqlAjaxController extends Controller
{
    public function __construct(
        private readonly SqlReadService $sqlReadService,
        Request $request
    ) {
        parent::__construct($request);
    }

    public function execute(): never
    {
        $sql = trim((string) $this->request->input('sql'));

        if ($sql === '')
        {
            throw new ValidationException(['sql' => 'Veuillez saisir une requête SQL.']);
        }

        try
        {
            $result = $this->sqlReadService->execute($sql);

            $this->jsonResult(ServiceResult::success(data: ['result' => $result]));
        }
        catch (Throwable $exception)
        {
            $this->jsonResult(ServiceResult::error(message: $exception->getMessage()));
        }
    }
}
