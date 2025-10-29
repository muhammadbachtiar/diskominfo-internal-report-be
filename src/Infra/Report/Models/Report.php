<?php
namespace Infra\Report\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Infra\Asset\Models\Asset;
use Infra\Report\Models\Evidence\Evidence;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;
use Infra\User\Models\User;
use Infra\Report\Models\Approval;
use Infra\Report\Models\Signature;
use Infra\Report\Models\Comment;
use Infra\Shared\Models\Unit;

class Report extends BaseModel
{
    use HasUuidV7;
    use SoftDeletes;
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function evidences()
    {
        return $this->hasMany(Evidence::class, 'report_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'report_assignees', 'report_id', 'user_id');
    }
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'report_id');
    }

    public function signature()
    {
        return $this->hasOne(Signature::class, 'report_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'report_assets', 'report_id', 'asset_id')
            ->withPivot('note')
            ->withTimestamps();
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ReportAction::class)->orderBy('sequence');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class);
    }
}
