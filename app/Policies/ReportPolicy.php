<?php

namespace App\Policies;

use Infra\Report\Models\Report;
use Infra\User\Models\User;

class ReportPolicy
{
    // public function view(User $user, Report $report): bool
    // {
    //     if ($this->hasRole($user, 'kadin')) {
    //         return true;
    //     }
    //     if ($this->hasRole($user, 'kabid')) {
    //         return $user->unit_id === $report->unit_id;
    //     }
    //     // pegawai: hanya yang ditugaskan atau pembuat
    //     if ($this->hasRole($user, 'pegawai')) {
    //         return $report->creator?->id === $user->id || $report->assignees()->where('users.id', $user->id)->exists();
    //     }
    //     // fallback: unit match
    //     return $user->unit_id === $report->unit_id || $this->isAdmin($user);
    // }

    // public function update(User $user, Report $report): bool
    // {
    //     if (in_array($report->status, ['approved','rejected'])) return false;
    //     if ($this->isAdmin($user) || $this->hasRole($user, 'kadin')) return true;
    //     if ($this->hasRole($user, 'kabid')) return $user->unit_id === $report->unit_id;
    //     // pegawai: hanya pembuat atau assignee
    //     if ($this->hasRole($user, 'pegawai')) {
    //         return ($report->creator?->id === $user->id) || $report->assignees()->where('users.id', $user->id)->exists();
    //     }
    //     return false;
    // }

    // public function review(User $user, Report $report): bool
    // {
    //     // Kabid role assumed named 'kabid'
    //     if ($this->isAdmin($user) || $this->hasRole($user, 'kadin')) return true;
    //     return $this->hasRole($user, 'kabid') && $user->unit_id === $report->unit_id;
    // }

    // public function delete(User $user, Report $report): bool
    // {
    //     if (in_array($report->status, ['approved','rejected'])) return false;
    //     if ($this->isAdmin($user) || $this->hasRole($user, 'kadin')) return true;
    //     if ($this->hasRole($user, 'kabid')) return $user->unit_id === $report->unit_id;
    //     // pegawai: hanya pembuat
    //     if ($this->hasRole($user, 'pegawai')) return $report->creator?->id === $user->id;
    //     return false;
    // }

    // public function assign(User $user, Report $report): bool
    // {
    //     // kelola assignees: kadin atau kabid satu unit, atau pembuat
    //     if ($this->isAdmin($user) || $this->hasRole($user, 'kadin')) return true;
    //     if ($this->hasRole($user, 'kabid')) return $user->unit_id === $report->unit_id;
    //     return $report->creator?->id === $user->id;
    // }

    // private function isAdmin(User $user): bool
    // {
    //     return $this->hasRole($user, 'admin');
    // }

    // private function hasRole(User $user, string $role): bool
    // {
    //     return (bool) $user->roles()->where('nama', $role)->exists();
    // }


    public function viewAny(User $user): bool
    {
        if ($this->isSuperadmin($user) || $this->isKadin($user)) {
            return true;
        }

        if ($this->isKabid($user) || $this->isPegawai($user)) {
            return true;
        }

        return false;
    }

    public function view(User $user, Report $report): bool
    {
        if ($this->isSuperadmin($user) || $this->isKadin($user)) {
            return true;
        }

        if ($this->isKabid($user)) {
            return $this->isSameUnit($user, $report);
        }

        if ($this->isPegawai($user)) {
            return $this->isCreator($user, $report) || $this->isAssignee($user, $report);
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($this->isSuperadmin($user) || $this->isKadin($user)) {
            return true;
        }

        if ($this->isKabid($user)) {
            return true;
        }

        return false;
    }

    public function update(User $user, Report $report): bool
    {
        if (!$this->isModifiable($report)) {
            return false;
        }

        if ($this->isSuperadmin($user) || $this->isKadin($user)) {
            return true;
        }

        if ($this->isKabid($user)) {
            return $this->isSameUnit($user, $report);
        }

        if ($this->isPegawai($user)) {
            return $this->isCreator($user, $report) || $this->isAssignee($user, $report);
        }

        return false;
    }

    public function delete(User $user, Report $report): bool
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }

        if (!$this->isModifiable($report)) {
            return false;
        }

        if ($this->isKadin($user)) {
            return true;
        }

        if ($this->isKabid($user)) {
            return $this->isSameUnit($user, $report);
        }

        if ($this->isPegawai($user)) {
            return $this->isCreator($user, $report);
        }

        return false;
    }

    public function assignAssetToReport(User $user, Report $report): bool
    {

        if ($this->isSuperadmin($user) || $this->isKadin($user)) {
            return true;
        }

        if ($this->isKabid($user)) {
            return $this->isSameUnit($user, $report);
        }

        if ($this->isPegawai($user)) {
            return $this->isAssignee($user, $report);
        }

        return false;
    }

    public function unassignAssetFromReport(User $user, Report $report): bool
    {
        return $this->assignAssetToReport($user, $report);
    }

    public function assignMemberToReport(User $user, Report $report): bool
    {
        if ($this->isSuperadmin($user) || $this->isKadin($user)) {
            return true;
        }

        if ($this->isKabid($user)) {
            return $this->isSameUnit($user, $report);
        }

        if ($this->isPegawai($user)) {
            return $this->isCreator($user, $report);
        }
        
        return false;
    }

    public function unassignMemberFromReport(User $user, Report $report): bool
    {
        return $this->assignMemberToReport($user, $report);
    }

    public function submitReport(User $user, Report $report): bool
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }

        if (!in_array($report->status, ['draft', 'revision'])) {
            return false;
        }

        if ($this->isPegawai($user)) {
            return $this->isAssignee($user, $report);
        }

        return false;
    }

    public function reviewReport(User $user, Report $report): bool
    {
        if ($this->isSuperadmin($user) || $this->isKadin($user)) {
            return true;
        }

        if ($report->status !== 'submitted') {
            return false;
        }

        if ($this->isKabid($user)) {
            return $this->isSameUnit($user, $report);
        }

        return false;
    }
    private function hasRole(User $user, string $role): bool
    {
        return (bool) $user->roles()->where('nama', $role)->exists();
    }
    
    private function isSuperadmin(User $user): bool
    {
        return $this->hasRole($user, 'admin');
    }


    private function isKadin(User $user): bool
    {
        return $this->hasRole($user, 'kadin');
    }

    private function isKabid(User $user): bool
    {
        return $this->hasRole($user, 'kabid');
    }

    private function isPegawai(User $user): bool
    {
        return $this->hasRole($user, 'pegawai');
    }

    private function isSameUnit(User $user, Report $report): bool
    {
        return $user->unit_id === $report->unit_id;
    }
    private function isCreator(User $user, Report $report): bool
    {
        return $report->created_by === $user->id;
    }

    private function isAssignee(User $user, Report $report): bool
    {
        return $report->assignees()->where('users.id', $user->id)->exists();
    }

    private function isModifiable(Report $report): bool
    {
        return !in_array($report->status, ['approved', 'rejected']);
    }
}
