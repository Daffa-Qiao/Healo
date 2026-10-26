<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthInsightsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['period' => ['sometimes', 'integer', Rule::in([7, 30, 90])]];
    }

    public function period(): int
    {
        return (int) ($this->validated('period') ?? 7);
    }
}
