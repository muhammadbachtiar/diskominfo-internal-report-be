<?php

namespace App\Http\Controllers\API\V1\Asset\CRUD;

use Domain\Asset\Actions\CRUD\DeleteAssetAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class DeleteAssetController extends BaseController
{
    public function __invoke(string $asset)
    {
        try {
            DeleteAssetAction::resolve()->execute($asset);

            return $this->resolveForSuccessResponseWith('Asset deleted');
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith('Asset not found');
        } catch (InvalidArgumentException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), [], HttpStatus::BadRequest);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

