<?php

namespace Domain\Asset\Actions\Attachment;

use Domain\Asset\Repositories\AssetAttachmentRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Infra\Asset\Models\Asset;
use Infra\Shared\Foundations\Action;

class IndexAttachmentAction extends Action
{
    public function __construct(
        private AssetAttachmentRepositoryInterface $attachments
    ) {
    }

    public function execute(Asset $asset, array $filters = []): LengthAwarePaginator|iterable
    {
        CheckRolesAction::resolve()->execute('view-asset');

        return $this->attachments->findByAssetId($asset->id, $filters);
    }
}