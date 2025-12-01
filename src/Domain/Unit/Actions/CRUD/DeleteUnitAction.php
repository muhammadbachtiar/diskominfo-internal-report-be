<?php

namespace Domain\Unit\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Foundations\Action;
use Infra\Shared\Models\Unit;

class DeleteUnitAction extends Action
{
    public function execute(Unit $unit): bool
    {
        CheckRolesAction::resolve()->execute('delete-unit');
        $unit->delete();
        return true;
    }
}

