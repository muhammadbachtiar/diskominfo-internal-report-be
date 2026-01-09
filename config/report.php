<?php

return [
    'max_gps_accuracy' => env('EVIDENCE_MAX_ACCURACY', 50),
    'evidence' => [
        'disk' => env('EVIDENCE_DISK', env('FILESYSTEM_DISK', 's3')),
        'bucket' => env('EVIDENCE_BUCKET', env('AWS_BUCKET')),
        'prefix' => env('EVIDENCE_PREFIX', 'reports'),
        'max_upload_size' => (int) env('EVIDENCE_MAX_SIZE', 20_000_000),
        'presign_ttl' => (int) env('EVIDENCE_PRESIGN_TTL', 900),
    ],
    // Allowed relationships for dynamic eager loading via ?with=
    'report_allowed_includes' => [
        'evidences',
        'creator',
        'user',
        'assignees',
        'approvals',
        'assignees.unit',
        'signature',
        'comments',
        'unit',
        'assets',
    ],
    'pagination' => [
        'per_page' => env('REPORTS_PER_PAGE', 10),
        'max_per_page' => env('REPORTS_MAX_PER_PAGE', 100),
    ],
];
