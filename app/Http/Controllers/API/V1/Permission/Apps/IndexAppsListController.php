<?php

namespace App\Http\Controllers\API\V1\Permission\Apps;

use Domain\Permissions\Actions\IndexAppsListAction;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexAppsListController extends BaseController
{
    public function __invoke()
    {

        try {
            $data = IndexAppsListAction::resolve()->execute();

            return $this->resolveForSuccessResponseWith(
                message: 'App list',
                data: $data
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::InternalError
            );
        }
    }
}
