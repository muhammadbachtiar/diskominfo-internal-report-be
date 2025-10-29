<?php

return [
    'provider' => env('TTE_PROVIDER', 'mock'),
    'signer_nik' => env('TTE_SIGNER_NIK', ''),
    'bsre' => [
        'base_url' => env('TTE_BSRE_BASE_URL'),
        'client_id' => env('TTE_BSRE_CLIENT_ID'),
        'client_secret' => env('TTE_BSRE_CLIENT_SECRET'),
    ],
];

