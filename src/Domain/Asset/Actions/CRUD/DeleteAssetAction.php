<?php

namespace Domain\Asset\Actions\CRUD;

use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetLoanRepositoryInterface;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Asset\Models\Asset as AssetModel;
use InvalidArgumentException;
use Infra\Shared\Foundations\Action;

class DeleteAssetAction extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetLoanRepositoryInterface $loans,
    ) {
    }

    public function execute(string $assetId): void
    {
        CheckRolesAction::resolve()->execute('delete-asset');

        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        $openLoan = $this->loans->findOpenLoanByAsset($assetId);

        static::assertDeletable($asset->status, $asset->id, $openLoan !== null);

        $this->assets->delete($assetId);

        AuditLogger::log('asset.delete', 'assets', $assetId);
    }

    /**
     * Shared validation logic used by both single and bulk delete.
     * An asset can be deleted if:
     *   (a) Its status is Retired, or
     *   (b) Its status is Available AND it has no transaction history
     *       (loans, maintenances, or report attachments).
     *
     * @throws InvalidArgumentException
     */
    public static function assertDeletable(
        AssetStatus $status,
        string $assetId,
        bool $hasOpenLoan,
    ): void {
        if ($hasOpenLoan) {
            throw new InvalidArgumentException(
                'Asset has an active loan and cannot be deleted.'
            );
        }

        if ($status === AssetStatus::Retired) {
            return; // always deletable once retired
        }

        if ($status === AssetStatus::Available) {
            // Allow deletion only when the asset has no transaction history
            $model = AssetModel::withCount(['loans', 'maintenances', 'reports'])->find($assetId);

            if ($model && $model->loans_count === 0 && $model->maintenances_count === 0 && $model->reports_count === 0) {
                return; // virgin available asset — safe to delete
            }

            throw new InvalidArgumentException(
                'Asset with status available can only be deleted if it has no transaction history (loans, maintenance, or reports).'
            );
        }

        throw new InvalidArgumentException(
            'Only retired assets or available assets without any history can be deleted.'
        );
    }
}

