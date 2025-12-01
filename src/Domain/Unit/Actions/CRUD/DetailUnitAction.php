<?php

namespace Domain\Unit\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Foundations\Action;
use Infra\Shared\Models\Unit;

class DetailUnitAction extends Action
{
    public function execute(Unit $unit): Unit
    {
        CheckRolesAction::resolve()->execute('view-unit');
        return $unit;
    }
}

