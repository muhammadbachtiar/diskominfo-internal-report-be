<?php

namespace Domain\Report\Enums;

enum ReportStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Review = 'review';
    case Revision = 'revision';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }
}

