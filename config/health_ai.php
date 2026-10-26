<?php

return [
    'provider' => env('HEALO_AI_PROVIDER', 'local_demo'),
    'request_timeout' => (int) env('HEALO_AI_REQUEST_TIMEOUT', 30),
    'rate_limit_per_minute' => (int) env('HEALO_AI_RATE_LIMIT', 10),
    'message_max_length' => 3000,
    'history_message_limit' => 12,
    'recent_journal_limit' => 3,
    'provider_context_notice' => true,
    'emergency' => [
        'phone' => env('HEALO_EMERGENCY_PHONE'),
        'facility_url' => env('HEALO_EMERGENCY_FACILITY_URL'),
    ],
];
