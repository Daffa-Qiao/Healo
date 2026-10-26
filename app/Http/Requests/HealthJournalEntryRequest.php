<?php

namespace App\Http\Requests;

use App\Enums\JournalEntryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['content' => trim(strip_tags((string) $this->input('content')))]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(JournalEntryType::class)],
            'content' => [
                'required',
                'string',
                Rule::when(
                    $this->input('type') === JournalEntryType::Symptom->value,
                    ['max:120'],
                    ['max:5000'],
                ),
            ],
        ];
    }
}
