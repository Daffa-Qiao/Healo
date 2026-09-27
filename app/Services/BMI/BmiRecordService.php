<?php

namespace App\Services\BMI;

use App\Models\BmiRecord;
use App\Models\User;
use Illuminate\Support\Arr;

class BmiRecordService
{
    public function __construct(private readonly BmiCalculator $calculator) {}

    /** @param array<string, mixed> $data */
    public function record(User $user, array $data): BmiRecord
    {
        $result = $this->calculator->calculate((float) $data['weight_kg'], (float) $data['height_cm']);

        $record = $user->bmiRecords()->create([
            ...Arr::only($data, ['height_cm', 'weight_kg']),
            'bmi' => $result->value,
            'category' => $result->category,
            'measured_at' => $data['measured_at'] ?? now(),
        ]);

        if (($data['update_health_profile'] ?? false) === true) {
            $profile = $user->healthProfile()->firstOrCreate();
            $profile->update(Arr::only($data, ['height_cm', 'weight_kg']));
        }

        return $record;
    }
}

