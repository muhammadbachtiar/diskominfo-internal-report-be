<?php

namespace Domain\Report\Enums;

enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Revision = 'revision';

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }
}

