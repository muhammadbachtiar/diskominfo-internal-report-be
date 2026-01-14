<?php

namespace App\Http\Controllers\API\V1\Asset\Location;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Asset\Models\Location;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexLocationController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('view-location');

            $query = Location::query();

            if ($search = $request->query('search')) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($request->query('select') === 'yes') {
                $data = $query->limit(100)->get();
                return $this->resolveForSuccessResponseWith('Locations', $data);
            }

            $data = $query->paginate($request->query('page_size', 10));
            return $this->resolveForSuccessResponseWithPage('Locations', $data);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
