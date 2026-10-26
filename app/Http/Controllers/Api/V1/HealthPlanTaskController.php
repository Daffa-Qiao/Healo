<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteHealthPlanTaskRequest;
use App\Http\Requests\UncompleteHealthPlanTaskRequest;
use App\Http\Resources\HealthPlanResource;
use App\Models\HealthPlanTask;
use App\Services\HealthPlan\HealthPlanService;

class HealthPlanTaskController extends Controller
{
    public function complete(CompleteHealthPlanTaskRequest $request, HealthPlanTask $task, HealthPlanService $plans): HealthPlanResource
    {
        return new HealthPlanResource($plans->complete($task));
    }

    public function uncomplete(UncompleteHealthPlanTaskRequest $request, HealthPlanTask $task, HealthPlanService $plans): HealthPlanResource
    {
        return new HealthPlanResource($plans->uncomplete($task));
    }
}
