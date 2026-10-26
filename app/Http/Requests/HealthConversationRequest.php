<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HealthConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['title' => $this->cleanText($this->input('title'))]);
    }

    public function rules(): array
    {
        return ['title' => ['nullable', 'string', 'max:160']];
    }

    private function cleanText(mixed $value): ?string
    {
        $clean = trim(strip_tags((string) $value));

        return $clean === '' ? null : $clean;
    }
}
