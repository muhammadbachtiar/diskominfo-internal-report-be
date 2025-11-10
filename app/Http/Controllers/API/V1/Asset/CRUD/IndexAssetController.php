<?php

namespace App\Http\Controllers\API\V1\Asset\CRUD;

use Domain\Asset\Actions\CRUD\IndexAssetAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexAssetController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            $result = IndexAssetAction::resolve()->execute($request->query());

            if ($request->query('select') === 'yes') {
                return $this->resolveForSuccessResponseWith('Assets', $result);
            }

            return $this->resolveForSuccessResponseWithPage('Assets', $result);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

