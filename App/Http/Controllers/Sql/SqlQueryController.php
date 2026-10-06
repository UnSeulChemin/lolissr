<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sql;

use App\DTO\Common\ServiceResult;
use App\Http\Controllers\Controller;
use App\Services\Sql\SqlExecutionService;

use Framework\Http\Exceptions\ValidationException;
use Framework\Http\Requests\Request;

use Throwable;

final class SqlQueryController extends Controller
{
    public function __construct(private readonly SqlExecutionService $sqlExecutionService, Request $request)
    {
        parent::__construct($request);
    }

    public function execute(): never
    {
        $sql = trim($this->stringInput('sql'));

        if ($sql === '')
        {
            throw new ValidationException(['sql' => 'Veuillez saisir une requête SQL.']);
        }

        try
        {
            $result = $this->sqlExecutionService->execute($sql);

            $this->jsonResult(ServiceResult::success(data: $result));
        }
        catch (Throwable $exception)
        {
            $this->jsonResult(ServiceResult::error(message: $exception->getMessage()));
        }
    }
}
