<?php

return [
    'food_analysis_provider' => env('HEALO_FOOD_ANALYSIS_PROVIDER', 'local_demo'),
    'image_disk' => 'local',
    'image_max_kilobytes' => 5120,
    'image_retention_days' => (int) env('HEALO_FOOD_IMAGE_RETENTION_DAYS', 30),
    'low_confidence_threshold' => 0.6,
    'calorie_target' => [
        'minimum' => 1200,
        'maximum' => 4500,
    ],
];
