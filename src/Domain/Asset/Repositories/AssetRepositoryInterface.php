<?php

namespace Domain\Asset\Repositories;

use Domain\Asset\Entities\Asset;
use Domain\Asset\Enums\AssetStatus;

interface AssetRepositoryInterface
{
    public function find(string $id): ?Asset;

    public function findByCode(string $code): ?Asset;

    public function save(Asset $asset): Asset;

    public function updateStatus(string $id, AssetStatus $status): void;

    public function updateLocation(string $id, ?string $locationId): void;

    public function delete(string $id): void;

    public function attachToReport(string $assetId, string $reportId, ?string $note = null): void;

    public function detachFromReport(string $assetId, string $reportId): void;
}
