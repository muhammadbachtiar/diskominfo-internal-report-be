<?php

namespace App\Policies;

use Infra\Report\Models\Report;
use Infra\User\Models\User;

class ReportPolicy
{
    public function view(User $user, Report $report): bool
    {
        if ($this->hasRole($user, 'kadin')) {
            return true;
        }
        if ($this->hasRole($user, 'kabid')) {
            return $user->unit_id === $report->unit_id;
        }
        // pegawai: hanya yang ditugaskan atau pembuat
        if ($this->hasRole($user, 'pegawai')) {
            return $report->creator?->id === $user->id || $report->assignees()->where('users.id', $user->id)->exists();
        }
        // fallback: unit match
        return $user->unit_id === $report->unit_id || $this->isAdmin($user);
    }

    public function update(User $user, Report $report): bool
    {
        if (in_array($report->status, ['approved','rejected'])) return false;
        if ($this->isAdmin($user) || $this->hasRole($user, 'kadin')) return true;
        if ($this->hasRole($user, 'kabid')) return $user->unit_id === $report->unit_id;
        // pegawai: hanya pembuat atau assignee
        if ($this->hasRole($user, 'pegawai')) {
            return ($report->creator?->id === $user->id) || $report->assignees()->where('users.id', $user->id)->exists();
        }
        return false;
    }

    public function review(User $user, Report $report): bool
    {
        // Kabid role assumed named 'kabid'
        return $this->hasRole($user, 'kabid') && $user->unit_id === $report->unit_id;
    }

    public function delete(User $user, Report $report): bool
    {
        if (in_array($report->status, ['approved','rejected'])) return false;
        if ($this->isAdmin($user) || $this->hasRole($user, 'kadin')) return true;
        if ($this->hasRole($user, 'kabid')) return $user->unit_id === $report->unit_id;
        // pegawai: hanya pembuat
        if ($this->hasRole($user, 'pegawai')) return $report->creator?->id === $user->id;
        return false;
    }

    public function assign(User $user, Report $report): bool
    {
        // kelola assignees: kadin atau kabid satu unit, atau pembuat
        if ($this->isAdmin($user) || $this->hasRole($user, 'kadin')) return true;
        if ($this->hasRole($user, 'kabid')) return $user->unit_id === $report->unit_id;
        return $report->creator?->id === $user->id;
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
