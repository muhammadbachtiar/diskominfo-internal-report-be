<?php

namespace App\Http\Controllers\API\V1\Asset\Loan;

use Domain\Asset\Services\BulkActivateAssetService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class BulkActivateAssetController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('activate-asset');

            $data = $request->validate([
                // ── Shared fields (applied to all assets) ──────────────────
                'location_id'             => ['required', 'uuid', 'exists:locations,id'],
                'borrower_id'             => ['nullable', 'integer'],

                // ── Global defaults (optional, overridable per asset) ───────
                'global_pic'              => ['nullable', 'string', 'max:255'],
                'global_note'             => ['nullable', 'string'],

                // ── Per-asset list ─────────────────────────────────────────
                'assets'                  => ['required', 'array', 'min:1'],
                'assets.*.asset_id'       => ['required', 'string', 'exists:assets,id'],
                'assets.*.pic'            => ['nullable', 'string', 'max:255'],
                'assets.*.note'           => ['nullable', 'string'],
            ]);

            $result = BulkActivateAssetService::resolve()->execute($data);

            return $this->resolveForSuccessResponseWith(
                'Assets activated successfully',
                [
                    'activated_count' => $result['activated_count'],
                ]
            );
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith(
                'Validation Error',
                $e->errors(),
                HttpStatus::UnprocessableEntity
            );
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith(
                'One or more assets or the location were not found',
                [],
                HttpStatus::NotFound
            );
        } catch (InvalidArgumentException $e) {
            return $this->resolveForFailedResponseWith(
                $e->getMessage(),
                [],
                HttpStatus::BadRequest
            );
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
