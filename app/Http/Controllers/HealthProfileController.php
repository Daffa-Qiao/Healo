<?php

namespace App\Http\Controllers;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\HealthGoal;
use App\Http\Requests\HealthProfileRequest;
use App\Services\Audit\AuditLogService;
use App\Services\Health\HealthProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->healthProfile()->first();

        if ($profile) {
            $this->authorize('view', $profile);
        }

        return view('health-profile.edit', [
            'profile' => $profile,
            'genders' => Gender::cases(),
            'activityLevels' => ActivityLevel::cases(),
            'healthGoals' => HealthGoal::cases(),
        ]);
    }

    public function update(
        HealthProfileRequest $request,
        HealthProfileService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $existing = $request->user()->healthProfile()->first();
        if ($existing) {
            $this->authorize('update', $existing);
        }

        $profile = $service->update($request->user(), $request->validated());
        $audit->record($request->user(), 'health_profile.updated', $profile, request: $request);

        return redirect()->route('health-profile.edit')->with('status', 'health-profile-updated');
    }
}
