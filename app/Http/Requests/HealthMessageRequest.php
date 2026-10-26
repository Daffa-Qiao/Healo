<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HealthMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['message' => trim(strip_tags((string) $this->input('message')))]);
    }

    public function rules(): array
    {
        return ['message' => ['required', 'string', 'max:'.config('health_ai.message_max_length', 3000)]];
    }
}
