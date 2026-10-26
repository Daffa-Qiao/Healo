<?php

return [
    'demo_password' => env('HEALO_DEMO_PASSWORD', 'password'),

    'features' => [
        'notifications' => ['icon' => 'bell', 'title' => 'notifications', 'route' => 'notifications.index'],
    ],
];
