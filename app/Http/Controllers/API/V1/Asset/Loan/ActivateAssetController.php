<?php

namespace App\Http\Controllers\API\V1\Asset\Loan;

use Domain\Asset\Services\BorrowAssetService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class ActivateAssetController extends BaseController
{
    public function __invoke(Request $request, string $asset)
    {
        try {
            CheckRolesAction::resolve()->execute('activate-asset');

                        $data = $request->validate([
                'location_id' => ['required', 'uuid', 'exists:locations,id'],
            ]);

            $loan = BorrowAssetService::resolve()->execute(
                $asset,
                (int) $data['borrower_id'],
                array_key_exists('lat', $data) ? (float) $data['lat'] : null,
                array_key_exists('long', $data) ? (float) $data['long'] : null,
                $data['location_name'] ?? null,
                $data['pic'] ?? null,
                $data['note'] ?? null,
            );

            return $this->resolveForSuccessResponseWith('Asset activated', $loan);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith('Asset not found');
        } catch (InvalidArgumentException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), [], HttpStatus::BadRequest);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
