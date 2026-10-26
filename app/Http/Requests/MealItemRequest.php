<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'food_name' => trim(strip_tags((string) $this->input('food_name'))),
            'serving_unit' => trim(strip_tags((string) $this->input('serving_unit'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'food_name' => ['required', 'string', 'max:160'],
            'serving_size' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'serving_unit' => ['required', 'string', 'max:30'],
            'calories' => ['required', 'numeric', 'min:0', 'max:10000'],
            'protein_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'carbohydrates_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'fat_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ];
    }
}
