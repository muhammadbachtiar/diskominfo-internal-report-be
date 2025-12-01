<?php

namespace Domain\Unit\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Shared\Foundations\Action;
use Infra\Shared\Models\Unit;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateUnitAction extends Action
{
    public function execute(array $data, Unit $unit): Unit
    {
        CheckRolesAction::resolve()->execute('edit-unit');

        if (Arr::exists($data, 'code')) {
            $exists = Unit::where('code', $data['code'])->where('id', '!=', $unit->id)->exists();
            if ($exists) {
                throw new BadRequestException('code has been used');
            }
        }

        $unit->update(Arr::only($data, ['name','code']));
        return $unit;
    }
}

