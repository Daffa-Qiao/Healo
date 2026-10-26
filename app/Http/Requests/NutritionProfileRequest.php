<?php

namespace App\Http\Requests;

use App\Enums\ActivityLevel;
use App\Enums\DietPreference;
use App\Enums\NutritionGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NutritionProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $preferences = $this->input('food_preferences');
        if (is_string($preferences)) {
            $preferences = preg_split('/[\r\n,]+/', $preferences) ?: [];
        }

        $this->merge([
            'food_preferences' => collect(is_array($preferences) ? $preferences : [])
                ->map(fn (mixed $item): string => trim(strip_tags((string) $item)))
                ->filter()->values()->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'activity_level' => ['required', Rule::enum(ActivityLevel::class)],
            'goal' => ['required', Rule::enum(NutritionGoal::class)],
            'diet_preference' => ['required', Rule::enum(DietPreference::class)],
            'food_preferences' => ['array', 'max:20'],
            'food_preferences.*' => ['string', 'max:120', 'distinct:ignore_case'],
            'daily_calorie_target' => ['nullable', 'integer', 'between:1200,4500'],
            'recalculate' => ['sometimes', 'boolean'],
        ];
    }
}
