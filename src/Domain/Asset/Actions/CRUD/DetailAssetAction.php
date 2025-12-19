<?php

namespace Domain\Asset\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Asset\Models\Asset;
use Infra\Shared\Foundations\Action;

class DetailAssetAction extends Action
{
    public function execute(string $assetId): Asset
    {
        CheckRolesAction::resolve()->execute('view-asset');

        $asset = Asset::query()
            ->with(['unit', 'currentLoan.borrower', 'maintenances', 'statusHistories.actor'])
            ->find($assetId);

        if (! $asset) {
            throw (new ModelNotFoundException())->setModel(Asset::class, [$assetId]);
        }

        return $asset;
    }
}

