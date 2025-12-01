<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetMaintenance;
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

class MarkAssetMaintenanceService extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetLoanRepositoryInterface $loans,
        protected AssetMaintenanceRepositoryInterface $maintenances,
        protected AssetStatusHistoryRepositoryInterface $statusHistories,
        protected SendAppNotificationAction $notifier,
    ) {
    }

    public function start(
        string $assetId,
        string $description,
        ?int $performedBy = null,
        bool $returnToActiveLocation = true,
    ): AssetMaintenance
    {
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        if (! $asset->status->canTransitionTo(AssetStatus::Maintenance)) {
            throw new InvalidArgumentException('Asset cannot enter maintenance from current status.');
        }

        $activeMaintenance = $this->maintenances->findActiveByAsset($assetId);
        if ($activeMaintenance) {
            throw new InvalidArgumentException('Asset already has active maintenance.');
        }

        $maintenance = new AssetMaintenance(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            description: $description,
            startedAt: CarbonImmutable::now(),
            performedBy: $performedBy,
            returnToActiveLocation: $returnToActiveLocation,
        );
        $maintenance = $this->maintenances->create($maintenance);

        $this->assets->updateStatus($assetId, AssetStatus::Maintenance);
        $this->statusHistories->record(new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: AssetStatus::Maintenance,
            changedAt: CarbonImmutable::now(),
            changedBy: auth()->id(),
            note: 'Asset moved to maintenance',
        ));

        AuditLogger::log('asset.maintenance.start', 'asset_maintenances', $maintenance->id, [
            'asset_id' => $assetId,
            'description' => $description,
            'return_to_active_location' => $returnToActiveLocation,
        ]);

        $this->notifyAdmins([
            'event' => 'asset.maintenance.started',
            'asset_id' => $assetId,
            'maintenance_id' => $maintenance->id,
            'return_to_active_location' => $returnToActiveLocation,
        ]);

        return $maintenance;
    }

    public function complete(string $assetId, ?int $actorId = null, ?string $note = null): AssetMaintenance
    {
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        $maintenance = $this->maintenances->findActiveByAsset($assetId);
        if (! $maintenance) {
            throw new InvalidArgumentException('Asset has no active maintenance entry.');
        }

        // Load the Eloquent model to call markAsCompleted which handles completion_note and completed_by
        $maintenanceModel = \Infra\Asset\Models\AssetMaintenance::query()->findOrFail($maintenance->id);
        $maintenanceModel->markAsCompleted($note, $actorId);

        // Update asset status based on return_to_active_location
        // If return_to_active_location is false, asset stays with borrower (borrowed status if there's an active loan)
        // If return_to_active_location is true, asset physically returns (available status)
        $currentLoan = $this->loans->findOpenLoanByAsset($assetId);
        if ($maintenance->returnToActiveLocation) {
            $newStatus = AssetStatus::Available;
        } else {
            // Check if asset has an active loan
            $newStatus = $currentLoan ? AssetStatus::Borrowed : AssetStatus::Available;
        }
        $this->assets->updateStatus($assetId, $newStatus);

        $this->statusHistories->record(new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: $newStatus,
            changedAt: CarbonImmutable::now(),
            changedBy: $actorId ?? auth()->id(),
            note: $note ?? 'Maintenance completed',
        ));

        AuditLogger::log('asset.maintenance.complete', 'asset_maintenances', $maintenance->id, [
            'asset_id' => $assetId,
            'finished_at' => now()->toIso8601String(),
            'return_to_active_location' => $maintenance->returnToActiveLocation,
        ]);

        $this->notifyAdmins([
            'event' => 'asset.maintenance.completed',
            'asset_id' => $assetId,
            'maintenance_id' => $maintenance->id,
            'return_to_active_location' => $maintenance->returnToActiveLocation,
        ]);

        // Reload the maintenance entity from the updated model
        return $this->maintenances->findActiveByAsset($assetId) ?? $maintenance;
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
