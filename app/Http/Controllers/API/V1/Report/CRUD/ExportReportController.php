<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\ExportReportsAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class ExportReportController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $format = $req->query('format', 'csv');
            $res = ExportReportsAction::resolve()->execute($format);
            return response($res['content'], 200, [
                'Content-Type' => $res['content_type'],
                'Content-Disposition' => 'attachment; filename="'.$res['filename'].'"',
            ]);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
