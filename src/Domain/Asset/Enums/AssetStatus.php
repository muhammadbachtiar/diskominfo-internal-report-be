<?php

namespace Domain\Asset\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Borrowed = 'borrowed';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
    case Completed = 'completed'; 

    case Attached = 'attached';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Available => in_array($next, [self::Borrowed, self::Maintenance, self::Retired], true),
            self::Borrowed => in_array($next, [self::Available, self::Maintenance], true),
            self::Maintenance => in_array($next, [self::Available, self::Retired], true),
            self::Retired => $next === self::Retired,
            self::Completed => $next === self::Completed,
            self::Attached => $next === self::Attached
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Borrowed => 'Borrowed',
            self::Maintenance => 'Maintenance',
            self::Retired => 'Retired',
            self::Completed => 'Completed',
            self::Attached => 'Attached',
        };
    }

    public function isMutable(): bool
    {
        return $this !== self::Retired;
    }
}
