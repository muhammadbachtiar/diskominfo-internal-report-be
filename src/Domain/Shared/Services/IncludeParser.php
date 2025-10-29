<?php

namespace Domain\Shared\Services;

class IncludeParser
{
    public static function parse(null|string|array $with, array $allowed): array
    {
        if (is_string($with)) {
            $parts = array_filter(array_map('trim', explode(',', $with)));
        } elseif (is_array($with)) {
            $parts = $with;
        } else {
            $parts = [];
        }
        // Intersect with allowlist
        $parts = array_values(array_intersect($parts, $allowed));
        return $parts;
    }
}

