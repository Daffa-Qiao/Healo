<?php

namespace App\Http\Requests;

use App\Enums\MealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $notes = trim(strip_tags((string) $this->input('notes')));
        $this->merge(['notes' => $notes === '' ? null : $notes]);
    }

    public function rules(): array
    {
        return [
            'meal_date' => ['required', 'date', 'before_or_equal:today'],
            'meal_type' => ['required', Rule::enum(MealType::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
