<?php

namespace Domain\Asset\Actions\CRUD;

use Domain\Asset\Repositories\AssetLoanRepositoryInterface;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Infra\Shared\Foundations\Action;

class BulkDeleteAssetAction extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetLoanRepositoryInterface $loans,
    ) {
    }

    /**
     * Bulk-delete a list of assets.
     *
     * Validation rules (same as single delete):
     *   - Asset must exist.
     *   - Asset must be either Retired, or Available with no transaction history.
     *   - Asset must not have an active (open) loan.
     *
     * All assets are validated first before any deletion occurs.
     * If any asset fails validation the entire operation is aborted.
     *
     * @param  string[]  $assetIds
     * @return array{deleted_count: int, deleted_ids: string[]}
     *
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     */
    public function execute(array $assetIds): array
    {
        CheckRolesAction::resolve()->execute('delete-asset');

        // ── 1. Validate all assets first (fail-fast before touching DB) ──────
        foreach ($assetIds as $assetId) {
            $asset = $this->assets->find($assetId);

            if (! $asset) {
                throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
            }

            $openLoan = $this->loans->findOpenLoanByAsset($assetId);

            // Reuse exactly the same validation logic as single DeleteAssetAction
            DeleteAssetAction::assertDeletable($asset->status, $asset->id, $openLoan !== null);
        }

        // ── 2. Perform deletions ──────────────────────────────────────────────
        foreach ($assetIds as $assetId) {
            $this->assets->delete($assetId);
            AuditLogger::log('asset.delete', 'assets', $assetId);
        }

        return [
            'deleted_count' => count($assetIds),
            'deleted_ids'   => array_values($assetIds),
        ];
    }
}
