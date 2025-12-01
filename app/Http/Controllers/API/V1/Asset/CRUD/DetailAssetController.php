<?php

namespace App\Http\Controllers\API\V1\Asset\CRUD;

use Domain\Asset\Actions\CRUD\DetailAssetAction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Shared\Controllers\BaseController;

class DetailAssetController extends BaseController
{
    public function __invoke(Request $request, string $asset)
    {
        try {
            $data = DetailAssetAction::resolve()->execute($asset);

            return $this->resolveForSuccessResponseWith('Asset detail', $data);
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith('Asset not found');
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

