<?php

namespace Infra\Shared\Enums\Traits;

use BackedEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait hasCaseResolve
{
    public static function getCases(): array
    {
        $cases = [];

        foreach (self::cases() as $case) {
            $cases[] = $case->value ?? $case->name;
        }

        return $cases;
    }

    public static function getCaseOptions(): array
    {
        $cases = [];

        foreach (self::cases() as $case) {
            $cases[$case->name] = $case->value ?? $case->name;
        }

        return $cases;
    }

    public static function values(): array
    {
        $cases = static::cases();

        return isset($cases[0]) && $cases[0] instanceof BackedEnum
            ? array_column($cases, 'value')
            : array_column($cases, 'name');
    }

    public static function options(): array
    {
        return Collection::wrap(self::values())
            ->mapWithKeys(fn ($value) => [$value => Str::headline($value)])
            ->toArray();
    }
}
