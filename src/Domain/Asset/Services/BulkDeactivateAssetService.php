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
use Illuminate\Support\Facades\DB;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class BulkDeactivateAssetService extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetLoanRepositoryInterface $loans,
        protected AssetStatusHistoryRepositoryInterface $statusHistories,
        protected SendAppNotificationAction $notifier,
    ) {
    }

    /**
     * Bulk-deactivate a list of assets using "Global Default & Local Override" pattern.
     *
     * - global_note  : default note — overridable per asset
     * - actor_id     : shared actor who performed the return
     * - assets[].asset_id : the asset to deactivate
     * - assets[].note     : (optional) overrides global_note for this asset
     *
     * All assets are validated FIRST before any write occurs (fail-fast).
     * Wrapped in a DB transaction for atomicity.
     * Notification is sent ONCE as a summary (not per asset) to avoid spam.
     *
     * @param  array{
     *   actor_id: int|null,
     *   global_note: string|null,
     *   assets: array<array{asset_id: string, note?: string|null}>
     * }  $payload
     *
     * @return array{deactivated_count: int}
     */
    public function execute(array $payload): array
    {
        $actorId    = isset($payload['actor_id']) ? (int) $payload['actor_id'] : (int) auth()->id();
        $globalNote = $payload['global_note'] ?? null;
        $assetItems = $payload['assets'];
        $changedBy  = (int) auth()->id(); // Always the authenticated user for the FK

        // ── 1. Fail-fast validation — check all assets before touching DB ─────
        foreach ($assetItems as $item) {
            $assetId = $item['asset_id'];
            $asset   = $this->assets->find($assetId);

            if (! $asset) {
                throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
            }

            $loan = $this->loans->findOpenLoanByAsset($assetId);
            if (! $loan || $asset->locationId === null) {
                throw new InvalidArgumentException(
                    "Asset [{$asset->name}] has no active loan to close and cannot be deactivated."
                );
            }
        }

        // ── 2. Process all assets inside a single transaction ─────────────────
        $deactivatedCount = DB::transaction(function () use ($assetItems, $actorId, $globalNote, $changedBy) {
            $count = 0;

            foreach ($assetItems as $item) {
                $assetId   = $item['asset_id'];
                $finalNote = $item['note'] ?? $globalNote ?? null;

                $loan = $this->loans->findOpenLoanByAsset($assetId);

                // Clear location
                UpdateAssetAction::resolve()->execute($assetId, [
                    'location_id' => null,
                ]);

                // Close the loan
                $returnedLoan = $loan->markReturned(CarbonImmutable::now());
                $this->loans->save($returnedLoan);

                // Record status history
                $historyNote = $finalNote
                    ? $finalNote
                    : 'Bulk deactivated';

                $this->statusHistories->record(new AssetStatusHistory(
                    id: (string) Uuid::uuid7(),
                    assetId: $assetId,
                    status: AssetStatus::Available,
                    changedAt: CarbonImmutable::now(),
                    changedBy: $changedBy, // Always auth user — satisfies FK constraint
                    note: $historyNote,
                ));

                AuditLogger::log('asset.bulk_deactivate', 'asset_loans', $loan->id, [
                    'asset_id'    => $assetId,
                    'returned_at' => $returnedLoan->returnedAt?->toIso8601String(),
                    'actor_id'    => $actorId,
                    'note'        => $finalNote,
                ]);

                // Notify borrower
                $this->notifyBorrower($loan->borrowerId, $assetId, $loan->id);

                $count++;
            }

            return $count;
        });

        // ── 3. Send ONE summary notification to admins ────────────────────────
        $this->notifyAdminsSummary($deactivatedCount);

        return [
            'deactivated_count' => $deactivatedCount,
        ];
    }

    /**
     * Notify each asset's borrower individually.
     */
    protected function notifyBorrower(int $borrowerId, string $assetId, string $loanId): void
    {
        $this->notifier->execute($borrowerId, [
            'event'    => 'asset.deactivated',
            'asset_id' => $assetId,
            'loan_id'  => $loanId,
        ]);
    }

    /**
     * Send one summary notification to all admins.
     */
    protected function notifyAdminsSummary(int $count): void
    {
        $summaryPayload = [
            'event'              => 'asset.bulk_deactivated',
            'deactivated_count'  => $count,
        ];

        $adminRole = config('asset.notify_admin_role', 'admin');
        $adminIds  = User::query()
            ->whereHas('roles', fn ($q) => $q->where('nama', $adminRole))
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->notifier->execute((int) $adminId, $summaryPayload);
        }
    }
}
