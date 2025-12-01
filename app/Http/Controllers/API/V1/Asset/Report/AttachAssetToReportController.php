<?php

namespace App\Http\Controllers\API\V1\Asset\Report;

use Domain\Asset\Services\AttachAssetToReportService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class AttachAssetToReportController extends BaseController
{
    public function __invoke(Request $request, string $asset, string $report)
    {
        try {
            CheckRolesAction::resolve()->execute('attach-asset-report');

            $data = $request->validate([
                'note' => ['nullable', 'string'],
            ]);

            AttachAssetToReportService::resolve()->execute($asset, $report, $data['note'] ?? null);

            return $this->resolveForSuccessResponseWith('Asset linked to report');
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

