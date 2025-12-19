<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
use Domain\Asset\Actions\CRUD\UpdateAssetAction;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetLoanRepositoryInterface;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Domain\Notification\Actions\SendAppNotificationAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class ReturnAssetService extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetLoanRepositoryInterface $loans,
        protected AssetStatusHistoryRepositoryInterface $statusHistories,
        protected SendAppNotificationAction $notifier,
    ) {
    }

    public function execute(string $assetId, int $actorId = null, ?string $note = null): void
    {
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        $loan = $this->loans->findOpenLoanByAsset($assetId);
        if (! $loan || $asset->locationId === null) {
            throw new InvalidArgumentException('Asset has no active loan to close.');
        }

        UpdateAssetAction::resolve()->execute($assetId, [
            'location_id' => null,
        ]);

        $returnedLoan = $loan->markReturned(CarbonImmutable::now());
        $this->loans->save($returnedLoan);
        $this->statusHistories->record($this->buildHistory($assetId, AssetStatus::Available, $note ? "$note" : 'Asset activated', $actorId));

        AuditLogger::log('asset.deactivate', 'asset_loans', $loan->id, [
            'asset_id' => $assetId,
            'returned_at' => $returnedLoan->returnedAt?->toIso8601String(),
        ]);

        $payload = [
            'event' => 'asset.deactivated',
            'asset_id' => $assetId,
            'loan_id' => $loan->id,
        ];
        $this->notifyBorrowerAndAdmins($loan->borrowerId, $payload);
    }

    protected function buildHistory(string $assetId, AssetStatus $status, string $note, ?int $actorId): AssetStatusHistory
    {
        return new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: $status,
            changedAt: CarbonImmutable::now(),
            changedBy: $actorId ?? auth()->id(),
            note: $note,
        );
    }

    protected function notifyBorrowerAndAdmins(int $borrowerId, array $payload): void
    {
        $this->notifier->execute($borrowerId, $payload);
        $adminRole = config('asset.notify_admin_role', 'admin');
        $adminIds = User::query()
            ->whereHas('roles', fn ($query) => $query->where('nama', $adminRole))
            ->pluck('id');
        foreach ($adminIds as $adminId) {
            $this->notifier->execute((int) $adminId, $payload);
        }
    }
}
