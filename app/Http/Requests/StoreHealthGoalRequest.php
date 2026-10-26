<?php

namespace App\Http\Requests;

use App\Enums\HealthGoalStatus;
use App\Enums\HealthGoalType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHealthGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(HealthGoalType::class)],
            'target_value' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'start_date' => ['required', 'date', 'before_or_equal:today'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', Rule::enum(HealthGoalStatus::class)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $type = HealthGoalType::tryFrom((string) $this->input('type'));
            $value = (float) $this->input('target_value');
            $ranges = [
                HealthGoalType::Weight->value => [20, 500],
                HealthGoalType::Bmi->value => [10, 60],
                HealthGoalType::DailyCalories->value => [800, 6000],
                HealthGoalType::JournalFrequency->value => [1, 30],
                HealthGoalType::NutritionLogging->value => [1, 50],
            ];

            if ($type && ($value < $ranges[$type->value][0] || $value > $ranges[$type->value][1])) {
                $validator->errors()->add('target_value', __('insights.validation_target_range'));
            }
        }];
    }
}
