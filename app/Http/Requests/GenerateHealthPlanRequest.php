<?php

namespace App\Http\Requests;

use App\Models\HealthPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateHealthPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', HealthPlan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'plan_date' => ['sometimes', 'date_format:Y-m-d', Rule::in([today()->toDateString()])],
            'progress' => ['prohibited'],
            'progress_percentage' => ['prohibited'],
            'user_id' => ['prohibited'],
            'tasks' => ['prohibited'],
        ];
    }
}
