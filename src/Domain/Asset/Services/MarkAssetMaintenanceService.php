<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetMaintenance;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
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
        if ($activeMaintenance || $asset->status === AssetStatus::Maintenance) {
            throw new InvalidArgumentException('Asset already has active maintenance.');
        }

        if($returnToActiveLocation && $asset->locationId !== null) {
            ReturnAssetService::resolve()->execute($assetId, auth()->id() ?? null, $description ?? null);
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
            note: $description 
                ? "$description"
                : 'Asset moved to maintenance',
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

    public function complete(string $assetId, ?int $actorId = null, string $description): AssetMaintenance
    {
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        $maintenance = $this->maintenances->findActiveByAsset($assetId);
        if (! $maintenance || $asset->status !== AssetStatus::Maintenance) {
            throw new InvalidArgumentException('Asset has no active maintenance entry.');
        }

        $completed = $maintenance->markCompleted(CarbonImmutable::now());
        $completed->completion_note = $description;
        $completed->completed_by = $actorId ?? auth()->id();
        $completed = $this->maintenances->save($completed);

        $this->assets->updateStatus($assetId,  AssetStatus::Available);

        $this->statusHistories->record(new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: AssetStatus::Completed,
            changedAt: CarbonImmutable::now(),
            changedBy: $actorId ?? auth()->id(),
            note:  $description ? "$description" : 'Maintenance completed'
        ));

        AuditLogger::log('asset.maintenance.complete', 'asset_maintenances', $maintenance->id, [
            'asset_id' => $assetId,
            'finished_at' => $completed->finishedAt?->toIso8601String(),
            'return_to_active_location' => $maintenance->returnToActiveLocation,
        ]);

        $this->notifyAdmins([
            'event' => 'asset.maintenance.completed',
            'asset_id' => $assetId,
            'maintenance_id' => $maintenance->id,
            'return_to_active_location' => $maintenance->returnToActiveLocation,
        ]);

        return $completed;
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
