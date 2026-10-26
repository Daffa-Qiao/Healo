<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteHealthPlanTaskRequest;
use App\Http\Requests\UncompleteHealthPlanTaskRequest;
use App\Models\HealthPlanTask;
use App\Services\HealthPlan\HealthPlanService;
use Illuminate\Http\RedirectResponse;

class HealthPlanTaskController extends Controller
{
    public function complete(CompleteHealthPlanTaskRequest $request, HealthPlanTask $task, HealthPlanService $plans): RedirectResponse
    {
        $plans->complete($task);

        return back()->with('status', __('wellness.task_completed'));
    }

    public function uncomplete(UncompleteHealthPlanTaskRequest $request, HealthPlanTask $task, HealthPlanService $plans): RedirectResponse
    {
        $plans->uncomplete($task);

        return back()->with('status', __('wellness.task_reopened'));
    }
}
