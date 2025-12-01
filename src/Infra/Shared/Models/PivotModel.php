<?php

namespace Infra\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PivotModel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
