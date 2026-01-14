<?php

namespace Infra\Letter\Models;

use Infra\Shared\Models\BaseModel;

class Classification extends BaseModel
{
    protected $table = 'classifications';

    protected $fillable = [
        'name',
        'description',
    ];
}
