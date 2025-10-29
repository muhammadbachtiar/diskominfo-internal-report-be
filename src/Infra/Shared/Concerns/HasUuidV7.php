<?php

namespace Infra\Shared\Concerns;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

trait HasUuidV7
{
    protected static function bootHasUuidV7(): void
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Uuid::uuid7();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}

