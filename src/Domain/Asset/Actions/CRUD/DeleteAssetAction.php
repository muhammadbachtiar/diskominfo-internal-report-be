<?php

namespace Domain\Asset\Actions\CRUD;

use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetLoanRepositoryInterface;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

        if ($asset->status !== AssetStatus::Retired) {
            throw new InvalidArgumentException('Only retired assets can be deleted.');
        }

        $openLoan = $this->loans->findOpenLoanByAsset($assetId);
        if ($openLoan) {
            throw new InvalidArgumentException('Asset has an active loan and cannot be deleted.');
        }

        $this->assets->delete($assetId);

        AuditLogger::log('asset.delete', 'assets', $assetId);
    }
}

