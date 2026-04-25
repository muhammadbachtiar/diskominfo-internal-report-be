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
use Illuminate\Support\Facades\DB;
use Infra\Asset\Models\Location;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class BulkActivateAssetService extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetLoanRepositoryInterface $loans,
        protected AssetLocationRepositoryInterface $locations,
        protected AssetStatusHistoryRepositoryInterface $statusHistories,
        protected SendAppNotificationAction $notifier,
    ) {
    }

    /**
     * Bulk-activate a list of assets using "Global Default & Local Override" pattern.
     *
     * - location_id  : shared for all assets (required)
     * - borrower_id  : shared for all assets (required)
     * - global_pic   : default PIC — overridable per asset
     * - global_note  : default note — overridable per asset
     * - assets[].asset_id  : the asset to activate
     * - assets[].pic       : (optional) overrides global_pic for this asset
     * - assets[].note      : (optional) overrides global_note for this asset
     *
     * All assets are validated FIRST before any write occurs (fail-fast).
     * Wrapped in a DB transaction for atomicity.
     * Notification is sent ONCE as a summary (not per asset) to avoid spam.
     *
     * @param  array{
     *   location_id: string,
     *   borrower_id: int,
     *   global_pic: string|null,
     *   global_note: string|null,
     *   assets: array<array{asset_id: string, pic?: string|null, note?: string|null}>
     * }  $payload
     *
     * @return array{activated_count: int, loans: AssetLoan[]}
     */
    public function execute(array $payload): array
    {
        $locationId   = $payload['location_id'];
        $borrowerId   = isset($payload['borrower_id']) ? (int) $payload['borrower_id'] : (int) auth()->id();
        $globalPic    = $payload['global_pic'] ?? null;
        $globalNote   = $payload['global_note'] ?? null;
        $assetItems   = $payload['assets'];
        $changedBy    = (int) auth()->id(); // Always auth user — satisfies FK constraint

        // ── 1. Load shared location once ─────────────────────────────────────
        $location = Location::find($locationId);
        if (! $location) {
            throw (new ModelNotFoundException())->setModel('locations', [$locationId]);
        }

        $this->assertValidGpsIfProvided($location->latitude, $location->longitude);

        // ── 2. Fail-fast validation — check all assets before touching DB ─────
        foreach ($assetItems as $item) {
            $assetId = $item['asset_id'];
            $asset   = $this->assets->find($assetId);

            if (! $asset) {
                throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
            }

            $existingLoan = $this->loans->findOpenLoanByAsset($assetId);
            if ($existingLoan || $asset->locationId !== null) {
                throw new InvalidArgumentException(
                    "Asset [{$asset->name}] currently has an active loan and cannot be activated."
                );
            }
        }

        // ── 3. Process all assets inside a single transaction ─────────────────
        $activatedLoans = DB::transaction(function () use (
            $assetItems, $locationId, $borrowerId,
            $globalPic, $globalNote, $location, $changedBy
        ) {
            $loans = [];

            foreach ($assetItems as $item) {
                $assetId = $item['asset_id'];

                // Global Default & Local Override pattern
                $finalPic  = $item['pic']  ?? $globalPic  ?? null;
                $finalNote = $item['note'] ?? $globalNote ?? null;

                // Update asset location_id
                UpdateAssetAction::resolve()->execute($assetId, [
                    'location_id' => $locationId,
                ]);

                // Create loan record
                $loan = new AssetLoan(
                    id: (string) Uuid::uuid7(),
                    assetId: $assetId,
                    borrowerId: $borrowerId,
                    loanLat: $location->latitude,
                    loanLong: $location->longitude,
                    borrowedAt: CarbonImmutable::now(),
                    locationName: $location->name,
                    picName: $finalPic,
                    note: $finalNote,
                );
                $loan = $this->loans->create($loan);

                // Record GPS location if available
                if ($location->latitude !== null && $location->longitude !== null) {
                    $this->locations->record(new AssetLocation(
                        id: (string) Uuid::uuid7(),
                        assetLoanId: $loan->id,
                        lat: $location->latitude,
                        longitude: $location->longitude,
                        locationName: $location->name,
                    ));
                }

                // Record status history
                $historyNote = $finalNote
                    ? "{$finalNote} | Lokasi : {$location->name}" . ($finalPic ? " ({$finalPic})" : '')
                    : "Bulk activated | Lokasi : {$location->name}";

                $this->statusHistories->record(new AssetStatusHistory(
                    id: (string) Uuid::uuid7(),
                    assetId: $assetId,
                    status: AssetStatus::Borrowed,
                    changedAt: CarbonImmutable::now(),
                    changedBy: $changedBy, // Always auth user — satisfies FK constraint
                    note: $historyNote,
                ));

                AuditLogger::log('asset.bulk_activate', 'asset_loans', $loan->id, [
                    'asset_id'   => $assetId,
                    'borrower_id' => $borrowerId,
                    'location'    => [
                        'lat'  => $location->latitude,
                        'long' => $location->longitude,
                        'name' => $location->name,
                    ],
                    'pic_name' => $finalPic,
                    'note'     => $finalNote,
                ]);

                $loans[] = $loan;
            }

            return $loans;
        });

        // ── 4. Send a SINGLE summary notification (no per-asset spam) ─────────
        $count = count($activatedLoans);
        $this->notifySummary($borrowerId, $count, $location->name);

        return [
            'activated_count' => $count,
            'loans'           => $activatedLoans,
        ];
    }

    /**
     * Send one summary notification to the borrower and all admins.
     */
    protected function notifySummary(int $borrowerId, int $count, string $locationName): void
    {
        $summaryPayload = [
            'event'         => 'asset.bulk_activated',
            'activated_count' => $count,
            'location_name' => $locationName,
            'borrower_id'   => $borrowerId,
        ];

        $this->notifier->execute($borrowerId, $summaryPayload);

        $adminRole = config('asset.notify_admin_role', 'admin');
        $adminIds  = User::query()
            ->whereHas('roles', fn ($q) => $q->where('nama', $adminRole))
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->notifier->execute((int) $adminId, $summaryPayload);
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
