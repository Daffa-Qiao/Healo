<?php

namespace App\Http\Controllers;

use App\Enums\HealthGoalStatus;
use App\Enums\HealthGoalType;
use App\Http\Requests\StoreHealthGoalRequest;
use App\Http\Requests\UpdateHealthGoalRequest;
use App\Models\HealthGoal;
use App\Services\Audit\AuditLogService;
use App\Services\Insights\HealthGoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthGoalController extends Controller
{
    public function index(Request $request, HealthGoalService $service): View
    {
        $goals = $request->user()->healthGoals()->latest()->paginate(15);
        $goals->getCollection()->each(fn (HealthGoal $goal) => $goal->setRelation('user', $request->user()));

        return view('health-goals.index', compact('goals', 'service'));
    }

    public function create(): View
    {
        return view('health-goals.create', [
            'types' => HealthGoalType::cases(),
            'statuses' => HealthGoalStatus::cases(),
        ]);
    }

    public function store(StoreHealthGoalRequest $request, HealthGoalService $service, AuditLogService $audit): RedirectResponse
    {
        $goal = $service->create($request->user(), $request->validated());
        $audit->record($request->user(), 'health_goal.created', $goal, request: $request);

        return redirect()->route('health-goals.index')->with('status', 'health-goal-created');
    }

    public function edit(Request $request, HealthGoal $goal): View
    {
        $this->authorize('update', $goal);

        return view('health-goals.edit', [
            'goal' => $goal,
            'statuses' => HealthGoalStatus::cases(),
        ]);
    }

    public function update(UpdateHealthGoalRequest $request, HealthGoal $goal, HealthGoalService $service, AuditLogService $audit): RedirectResponse
    {
        $goal = $service->update($goal, $request->validated());
        $audit->record($request->user(), 'health_goal.updated', $goal, request: $request);

        return redirect()->route('health-goals.index')->with('status', 'health-goal-updated');
    }

    public function destroy(Request $request, HealthGoal $goal, HealthGoalService $service, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('delete', $goal);
        $audit->record($request->user(), 'health_goal.deleted', $goal, request: $request);
        $service->delete($goal);

        return redirect()->route('health-goals.index')->with('status', 'health-goal-deleted');
    }
}
