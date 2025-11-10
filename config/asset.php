<?php

return [
    'notify_admin_role' => env('ASSET_NOTIFY_ADMIN_ROLE', 'admin'),

    'storage' => [
        'evidence_disk' => env('ASSET_EVIDENCE_DISK', 'evidence'),
        'pdf_disk' => env('ASSET_PDF_DISK', 'reports'),
    ],

    'antivirus' => [
        'enabled' => env('ASSET_ANTIVIRUS_ENABLED', false),
        'host' => env('CLAMAV_HOST', 'clamav'),
        'port' => env('CLAMAV_PORT', 3310),
    ],
];
