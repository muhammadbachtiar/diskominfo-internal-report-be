<?php

namespace Domain\Asset\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Borrowed = 'borrowed';
    case Maintenance = 'maintenance';
    case Retired = 'retired';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Available => in_array($next, [self::Borrowed, self::Maintenance, self::Retired], true),
            self::Borrowed => in_array($next, [self::Available, self::Maintenance], true),
            self::Maintenance => in_array($next, [self::Available, self::Retired], true),
            self::Retired => $next === self::Retired,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Borrowed => 'Borrowed',
            self::Maintenance => 'Maintenance',
            self::Retired => 'Retired',
        };
    }

    public function isMutable(): bool
    {
        return $this !== self::Retired;
    }
}
