<?php

namespace App\Policies;

use Domain\Asset\Enums\AssetStatus;
use Infra\Asset\Models\Asset;
use Infra\User\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->hasRole($user, 'kabid') || $this->hasRole($user, 'auditor');
    }

    public function view(User $user, Asset $asset): bool
    {
        if ($this->isAdmin($user) || $this->hasRole($user, 'auditor')) {
            return true;
        }

        if ($this->hasRole($user, 'kabid')) {
            return $asset->unit_id === null || $asset->unit_id === $user->unit_id;
        }

        if ($this->hasRole($user, 'pelapor')) {
            return $asset->unit_id === null || $asset->unit_id === $user->unit_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->hasRole($user, 'kabid');
    }

    public function update(User $user, Asset $asset): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        if ($this->hasRole($user, 'kabid')) {
            return $asset->unit_id === null || $asset->unit_id === $user->unit_id;
        }
        return false;
    }

    public function delete(User $user, Asset $asset): bool
    {
        if (! $this->isAdmin($user) && ! $this->hasRole($user, 'kabid')) {
            return false;
        }
        return $asset->status === AssetStatus::Retired->value;
    }

    public function borrow(User $user, Asset $asset): bool
    {
        if ($asset->status !== AssetStatus::Available->value) {
            return false;
        }
        if ($this->isAdmin($user) || $this->hasRole($user, 'kabid')) {
            return $asset->unit_id === null || $asset->unit_id === $user->unit_id;
        }
        if ($this->hasRole($user, 'pelapor')) {
            return $asset->unit_id === null || $asset->unit_id === $user->unit_id;
        }
        return false;
    }

    public function returnAsset(User $user, Asset $asset): bool
    {
        if (! in_array($asset->status, [AssetStatus::Borrowed->value, AssetStatus::Maintenance->value], true)) {
            return false;
        }
        return $this->isAdmin($user) || $this->hasRole($user, 'kabid') || $this->hasRole($user, 'pelapor');
    }

    public function markMaintenance(User $user, Asset $asset): bool
    {
        if (! in_array($asset->status, [AssetStatus::Available->value, AssetStatus::Borrowed->value], true)) {
            return false;
        }
        return $this->isAdmin($user) || $this->hasRole($user, 'kabid');
    }

    public function completeMaintenance(User $user, Asset $asset): bool
    {
        if ($asset->status !== AssetStatus::Maintenance->value) {
            return false;
        }
        return $this->isAdmin($user) || $this->hasRole($user, 'kabid');
    }

    public function retire(User $user, Asset $asset): bool
    {
        if (! in_array($asset->status, [AssetStatus::Available->value, AssetStatus::Maintenance->value], true)) {
            return false;
        }
        return $this->isAdmin($user) || $this->hasRole($user, 'kabid');
    }

    private function isAdmin(User $user): bool
    {
        return $this->hasRole($user, 'admin');
    }

    private function hasRole(User $user, string $role): bool
    {
        return (bool) $user->roles()->where('nama', $role)->exists();
    }
}
