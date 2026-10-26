<?php

namespace App\Http\Requests;

use App\Enums\MealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FoodAnalysisConfirmationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $foods = collect($this->input('foods', []))->map(function (mixed $food): mixed {
            if (! is_array($food)) {
                return $food;
            }

            $food['food_name'] = trim(strip_tags((string) ($food['food_name'] ?? '')));
            $food['serving_unit'] = trim(strip_tags((string) ($food['serving_unit'] ?? '')));

            return $food;
        })->all();
        $notes = trim(strip_tags((string) $this->input('notes')));
        $this->merge(['foods' => $foods, 'notes' => $notes === '' ? null : $notes]);
    }

    public function rules(): array
    {
        return [
            'meal_date' => ['required', 'date', 'before_or_equal:today'],
            'meal_type' => ['required', Rule::enum(MealType::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'foods' => ['required', 'array', 'min:1', 'max:20'],
            'foods.*.food_name' => ['required', 'string', 'max:160'],
            'foods.*.serving_size' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'foods.*.serving_unit' => ['required', 'string', 'max:30'],
            'foods.*.calories' => ['required', 'numeric', 'min:0', 'max:10000'],
            'foods.*.protein_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'foods.*.carbohydrates_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'foods.*.fat_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ];
    }
}
