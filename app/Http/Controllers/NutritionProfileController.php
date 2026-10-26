<?php

namespace App\Http\Controllers;

use App\Enums\ActivityLevel;
use App\Enums\DietPreference;
use App\Enums\NutritionGoal;
use App\Http\Requests\NutritionProfileRequest;
use App\Services\Audit\AuditLogService;
use App\Services\Nutrition\NutritionProfileService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NutritionProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->nutritionProfile()->first();
        if ($profile) {
            $this->authorize('view', $profile);
        }

        return view('nutrition.profile', [
            'profile' => $profile,
            'healthProfile' => $request->user()->healthProfile,
            'activityLevels' => ActivityLevel::cases(),
            'goals' => NutritionGoal::cases(),
            'dietPreferences' => DietPreference::cases(),
        ]);
    }

    public function update(
        NutritionProfileRequest $request,
        NutritionProfileService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $existing = $request->user()->nutritionProfile()->first();
        if ($existing) {
            $this->authorize('update', $existing);
        }

        try {
            $profile = $service->update($request->user(), $request->validated());
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['calorie_target' => $exception->getMessage()]);
        }

        $audit->record($request->user(), 'nutrition_profile.updated', $profile, request: $request);

        return redirect()->route('nutrition.profile.edit')->with('status', 'nutrition-profile-updated');
    }
}
