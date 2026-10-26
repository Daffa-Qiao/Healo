<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\HealthPlan\HealthPlanService;
use Illuminate\Database\Seeder;

class DemoHealthPlanSeeder extends Seeder
{
    public function run(HealthPlanService $plans): void
    {
        $user = User::query()->where('email', 'demo@healo.test')->firstOrFail();
        $plans->generate($user, today());
    }
}
