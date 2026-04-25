<?php

namespace App\Http\Controllers\API\V1\Asset\Loan;

use Domain\Asset\Services\BulkDeactivateAssetService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class BulkDeactivateAssetController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('deactivate-asset');

            $data = $request->validate([
                // ── Shared fields ────────────────────────────────────────────
                'actor_id'            => ['nullable', 'integer'],

                // ── Global defaults (optional, overridable per asset) ────────
                'global_note'         => ['nullable', 'string'],

                // ── Per-asset list ───────────────────────────────────────────
                'assets'              => ['required', 'array', 'min:1'],
                'assets.*.asset_id'   => ['required', 'string', 'exists:assets,id'],
                'assets.*.note'       => ['nullable', 'string'],
            ]);

            $result = BulkDeactivateAssetService::resolve()->execute($data);

            return $this->resolveForSuccessResponseWith(
                'Assets deactivated successfully',
                [
                    'deactivated_count' => $result['deactivated_count'],
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
                'One or more assets were not found',
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
