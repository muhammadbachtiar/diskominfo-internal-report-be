<?php

namespace App\Http\Controllers\API\V1\Asset\Location;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Asset\Models\Location;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class CreateLocationController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('manage-locations');

            $data = $request->validate([
                'name' => ['required', 'string'],
                'description' => ['nullable', 'string'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
            ]);

            $location = Location::create($data);

            return $this->resolveForSuccessResponseWith('Location created', $location, HttpStatus::Created);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
