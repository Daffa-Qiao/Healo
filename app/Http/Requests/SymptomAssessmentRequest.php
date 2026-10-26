<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SymptomAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['symptoms' => trim(strip_tags((string) $this->input('symptoms')))]);
    }

    public function rules(): array
    {
        return [
            'symptoms' => ['required', 'string', 'min:5', 'max:'.config('health_ai.message_max_length', 3000)],
            'save' => ['sometimes', 'boolean'],
            'conversation_id' => [
                'nullable',
                'integer',
                Rule::exists('health_conversations', 'id')->where('user_id', $this->user()?->id),
            ],
        ];
    }
}
