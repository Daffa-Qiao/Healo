<?php

namespace App\Http\Requests;

use App\Enums\HealthGoalStatus;
use App\Enums\HealthGoalType;
use App\Models\HealthGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateHealthGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $goal = $this->route('goal');

        return $this->user() !== null && $goal instanceof HealthGoal && $this->user()->can('update', $goal);
    }

    protected function prepareForValidation(): void
    {
        $goal = $this->route('goal');
        if ($goal instanceof HealthGoal) {
            $this->merge([
                'start_date' => $this->input('start_date', $goal->start_date->toDateString()),
                'status' => $this->input('status', $goal->status->value),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'target_value' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'start_date' => ['required', 'date', 'before_or_equal:today'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::enum(HealthGoalStatus::class)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $goal = $this->route('goal');
            if (! $goal instanceof HealthGoal) {
                return;
            }
            $ranges = [
                HealthGoalType::Weight->value => [20, 500],
                HealthGoalType::Bmi->value => [10, 60],
                HealthGoalType::DailyCalories->value => [800, 6000],
                HealthGoalType::JournalFrequency->value => [1, 30],
                HealthGoalType::NutritionLogging->value => [1, 50],
            ];
            $value = (float) $this->input('target_value');
            [$minimum, $maximum] = $ranges[$goal->type->value];
            if ($value < $minimum || $value > $maximum) {
                $validator->errors()->add('target_value', __('insights.validation_target_range'));
            }
        }];
    }
}
