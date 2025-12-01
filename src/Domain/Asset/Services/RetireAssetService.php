<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetLoanRepositoryInterface;
use Domain\Asset\Repositories\AssetMaintenanceRepositoryInterface;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Domain\Notification\Actions\SendAppNotificationAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class RetireAssetService extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetLoanRepositoryInterface $loans,
        protected AssetMaintenanceRepositoryInterface $maintenances,
        protected AssetStatusHistoryRepositoryInterface $statusHistories,
        protected SendAppNotificationAction $notifier,
    ) {
    }

    public function execute(string $assetId, ?string $note = null): void
    {
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        if (! $asset->status->canTransitionTo(AssetStatus::Retired)) {
            throw new InvalidArgumentException('Asset cannot be retired from current status.');
        }

        $openLoan = $this->loans->findOpenLoanByAsset($assetId);
        if ($openLoan) {
            throw new InvalidArgumentException('Asset has an active loan and cannot be retired.');
        }

        $activeMaintenance = $this->maintenances->findActiveByAsset($assetId);
        if ($activeMaintenance) {
            throw new InvalidArgumentException('Asset has unresolved maintenance record and cannot be retired.');
        }

        $this->assets->updateStatus($assetId, AssetStatus::Retired);
        $history = new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: AssetStatus::Retired,
            changedAt: CarbonImmutable::now(),
            changedBy: auth()->id(),
            note: $note ?? 'Asset retired',
        );
        $this->statusHistories->record($history);

        AuditLogger::log('asset.retire', 'assets', $assetId, ['note' => $note]);

        $this->notifyAdmins([
            'event' => 'asset.retired',
            'asset_id' => $assetId,
            'note' => $note,
        ]);
    }

    protected function notifyAdmins(array $payload): void
    {
        $adminRole = config('asset.notify_admin_role', 'admin');
        $adminIds = User::query()
            ->whereHas('roles', fn ($query) => $query->where('nama', $adminRole))
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->notifier->execute((int) $adminId, $payload);
        }
    }
}
