<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
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
        ?float $lat = null,
        ?float $long = null,
        ?string $locationName = null,
        ?string $picName = null,
        ?string $note = null,
    ): AssetLoan
    {
        // Fetch location details from database
        $location = Location::findOrFail($locationId);
        
        // Use provided coordinates or fall back to location's coordinates
        $finalLat = $lat ?? $location->lat;
        $finalLong = $long ?? $location->long;
        $finalLocationName = $locationName ?? $location->name;

        $this->assertValidGpsIfProvided($finalLat, $finalLong);
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        if (! $asset->status->canTransitionTo(AssetStatus::Borrowed)) {
            throw new InvalidArgumentException('Asset is not available to be borrowed.');
        }

        $existingLoan = $this->loans->findOpenLoanByAsset($assetId);
        if ($existingLoan) {
            throw new InvalidArgumentException('Asset currently has an active loan record.');
        }

        $loan = new AssetLoan(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            borrowerId: $borrowerId,
            locationId: $locationId,
            loanLat: $finalLat,
            loanLong: $finalLong,
            borrowedAt: CarbonImmutable::now(),
            locationName: $finalLocationName,
            picName: $picName,
            note: $note,
        );
        $loan = $this->loans->create($loan);

        if ($finalLat !== null && $finalLong !== null) {
            $this->locations->record(new AssetLocation(
                id: (string) Uuid::uuid7(),
                assetLoanId: $loan->id,
                lat: $finalLat,
                longitude: $finalLong,
                locationName: $finalLocationName,
            ));
        }

        $this->assets->updateStatus($assetId, AssetStatus::Borrowed);
        $this->assets->updateLocation($assetId, $locationId);
        $this->statusHistories->record(new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: AssetStatus::Borrowed,
            changedAt: CarbonImmutable::now(),
            changedBy: auth()->id(),
            note: 'Asset activated',
        ));

        AuditLogger::log('asset.activate', 'asset_loans', $loan->id, [
            'asset_id' => $assetId,
            'borrower_id' => $borrowerId,
            'location_id' => $locationId,
            'location' => ['lat' => $finalLat, 'long' => $finalLong, 'name' => $finalLocationName],
            'pic_name' => $picName,
            'note' => $note,
        ]);

        $this->notifyUsers([
            'event' => 'asset.activated',
            'asset_id' => $assetId,
            'borrower_id' => $borrowerId,
            'location_id' => $locationId,
            'location' => ['lat' => $finalLat, 'long' => $finalLong, 'name' => $finalLocationName],
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
