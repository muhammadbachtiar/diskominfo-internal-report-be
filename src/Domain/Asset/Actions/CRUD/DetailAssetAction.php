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
            ->with([
                'unit',
                'category',
                'currentLoan.borrower',
                'maintenances',
                'statusHistories.actor',
                'attachments' => function ($query) {
                    $query->with('uploader:id,name,email')
                        ->orderBy('created_at', 'desc');
                },
            ])
            ->find($assetId);

        if (! $asset) {
            throw (new ModelNotFoundException())->setModel(Asset::class, [$assetId]);
        }

        return $asset;
    }
}

