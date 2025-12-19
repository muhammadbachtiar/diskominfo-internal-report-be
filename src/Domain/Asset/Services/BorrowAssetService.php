<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
use Domain\Asset\Actions\CRUD\UpdateAssetAction;
use Domain\Asset\Entities\AssetLoan;
use Domain\Asset\Entities\AssetLocation;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetLoanRepositoryInterface;
use Domain\Asset\Repositories\AssetLocationRepositoryInterface;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Domain\Notification\Actions\SendAppNotificationAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Asset\Models\Location;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class BorrowAssetService extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetLoanRepositoryInterface $loans,
        protected AssetLocationRepositoryInterface $locations,
        protected AssetStatusHistoryRepositoryInterface $statusHistories,
        protected SendAppNotificationAction $notifier,
    ) {
    }

    public function execute(
        string $assetId,
        int $borrowerId,
        string $locationId,
        ?string $picName = null,
        ?string $note = null,
    ): AssetLoan
    {
   
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        $existingLoan = $this->loans->findOpenLoanByAsset($assetId);
        if ($existingLoan || $asset->locationId !== null) {
            throw new InvalidArgumentException('Asset currently has an active loan record.');
        }

        UpdateAssetAction::resolve()->execute($assetId, [
            'location_id' => $locationId,
        ]);

        $location = Location::findOrFail($locationId);

        $this->assertValidGpsIfProvided($location->latitude, $location->longitude);

        $loan = new AssetLoan(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            borrowerId: $borrowerId,
            loanLat: $location->latitude,
            loanLong: $location->longitude,
            borrowedAt: CarbonImmutable::now(),
            locationName: $location->name,
            picName: $picName,
            note: $note,
        );
        $loan = $this->loans->create($loan);

        if ($location->latitude !== null && $location->longitude !== null) {
            $this->locations->record(new AssetLocation(
                id: (string) Uuid::uuid7(),
                assetLoanId: $loan->id,
                lat: $location->latitude,
                longitude: $location->longitude,
                locationName: $location->name,
            ));
        }

        $this->statusHistories->record(new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: AssetStatus::Borrowed,
            changedAt: CarbonImmutable::now(),
            changedBy: auth()->id(),
            note: $note 
            ? "$note | Lokasi : $location->name ($picName)"
            : 'Asset activated',
        ));

        AuditLogger::log('asset.activate', 'asset_loans', $loan->id, [
            'asset_id' => $assetId,
            'borrower_id' => $borrowerId,
            'location' => ['lat' => $location->latitude, 'long' => $location->longitude, 'name' => $location->name],
            'pic_name' => $picName,
            'note' => $note,
        ]);

        $this->notifyUsers([
            'event' => 'asset.activated',
            'asset_id' => $assetId,
            'borrower_id' => $borrowerId,
            'location' => ['lat' => $location->latitude, 'long' => $location->longitude, 'name' => $location->name],
            'pic_name' => $picName,
            'note' => $note,
        ], $borrowerId);

        return $loan;
    }

    protected function notifyUsers(array $payload, int $borrowerId): void
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

    protected function assertValidGpsIfProvided(?float $lat, ?float $long): void
    {
        if (($lat === null) xor ($long === null)) {
            throw new InvalidArgumentException('Latitude and longitude must both be provided or omitted.');
        }

        if ($lat === null || $long === null) {
            return;
        }

        if ($lat < -90 || $lat > 90 || $long < -180 || $long > 180) {
            throw new InvalidArgumentException('GPS coordinates are out of range.');
        }
    }
}
