<?php

namespace App\Services\Health;

use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Support\Arr;

class HealthProfileService
{
    /** @param array<string, mixed> $data */
    public function update(User $user, array $data): HealthProfile
    {
        $profile = $user->healthProfile()->firstOrNew();
        $profile->fill(Arr::except($data, ['consent']));

        if (! $profile->consented_at && ($data['consent'] ?? false)) {
            $profile->consented_at = now();
        }

        $profile->save();

        return $profile;
    }
}
