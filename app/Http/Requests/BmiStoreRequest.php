<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BmiStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'height_cm' => ['required', 'numeric', 'between:80,250'],
            'weight_kg' => ['required', 'numeric', 'between:20,400'],
            'measured_at' => ['nullable', 'date', 'before_or_equal:now'],
            'update_health_profile' => ['sometimes', 'boolean'],
        ];
    }
}
