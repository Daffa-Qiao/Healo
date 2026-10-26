<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateHealthPlanRequest;
use App\Services\HealthPlan\HealthPlanPersonalizationService;
use App\Services\HealthPlan\HealthPlanService;
use App\Services\Nutrition\NutritionSummaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthPlanController extends Controller
{
    public function index(
        Request $request,
        HealthPlanService $plans,
        HealthPlanPersonalizationService $personalization,
        NutritionSummaryService $nutrition,
    ): View {
        $user = $request->user();
        $plan = $plans->today($user, false);

        return view('health-plan.index', [
            'plan' => $plan,
            'metrics' => $plan ? $plans->metrics($plan) : null,
            'personalization' => $plan ? $personalization->forPlan($plan) : null,
            'latestBmi' => $user->bmiRecords()->latest('measured_at')->first(),
            'todayNutrition' => $nutrition->refresh($user, today()),
            'activeGoalCount' => $user->healthGoals()->where('status', 'active')->count(),
            'hasJournalToday' => $user->healthJournals()->whereDate('journal_date', today())->exists(),
        ]);
    }

    public function generate(GenerateHealthPlanRequest $request, HealthPlanService $plans): RedirectResponse
    {
        $plans->generate($request->user(), $request->validated('plan_date'));

        return redirect()->route('health-plan.index')->with('status', __('wellness.plan_generated'));
    }
}
