<?php
namespace Infra\Report\Models;

use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;
use Infra\User\Models\User;

class Approval extends BaseModel
{
    use HasUuidV7;

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}

