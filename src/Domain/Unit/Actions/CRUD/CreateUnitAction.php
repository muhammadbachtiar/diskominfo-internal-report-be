<?php

namespace Domain\Unit\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Shared\Foundations\Action;
use Infra\Shared\Models\Unit;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreateUnitAction extends Action
{
    public function execute(array $data): Unit
    {
        CheckRolesAction::resolve()->execute('add-unit');

        foreach (['name','code'] as $key) {
            if (!Arr::exists($data, $key) || $data[$key] === null || $data[$key] === '') {
                throw new BadRequestException($key.' is required');
            }
        }

        $exists = Unit::where('code', $data['code'])->exists();
        if ($exists) {
            throw new BadRequestException('code has been used');
        }

        return Unit::create([
            'name' => $data['name'],
            'code' => $data['code'],
        ]);
    }
}

