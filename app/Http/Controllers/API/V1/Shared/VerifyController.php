<?php

namespace App\Http\Controllers\API\V1\Shared;

use Domain\Report\Actions\VerifyReportAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class VerifyController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $number = $req->query('number');
            $hash = $req->query('hash');
            $data = VerifyReportAction::resolve()->execute($number, $hash);
            return $this->resolveForSuccessResponseWith('Verification', $data);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\RuntimeException $th) {
            return $this->resolveForFailedResponseWith($th->getMessage(), status: HttpStatus::NotFound);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
