<?php

namespace App\Http\Controllers\API\V1\Asset\Report;

use Domain\Asset\DTOs\AttachAssetToReportInput;
use Domain\Asset\Services\AttachAssetToReportService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class AttachAssetToReportController extends BaseController
{
    /**
     * Attach single asset to report (backward compatibility)
     */
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

    /**
     * Attach multiple assets to report (batch operation)
     */
    public function batch(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('attach-asset-report');

            $data = $request->validate([
                'report_id' => ['required', 'uuid', 'exists:reports,id'],
                'assets' => ['required', 'array', 'min:1'],
                'assets.*.asset_id' => ['required', 'uuid', 'exists:assets,id'],
                'assets.*.note' => ['nullable', 'string'],
            ]);

            $input = AttachAssetToReportInput::fromArray($data);
            $results = AttachAssetToReportService::resolve()->executeBatch($input);

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $totalCount = count($results);

            return $this->resolveForSuccessResponseWith(
                "Successfully attached {$successCount} of {$totalCount} assets to report",
                ['results' => $results],
                HttpStatus::Ok
            );
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

