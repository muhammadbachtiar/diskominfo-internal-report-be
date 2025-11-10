<?php

namespace Infra\Shared\Foundations;

abstract class Action
{
    public static function resolve(): static
    {
        return app(static::class);
    }
}
