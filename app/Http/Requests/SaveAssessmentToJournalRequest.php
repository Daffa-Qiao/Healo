<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAssessmentToJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['title' => trim(strip_tags((string) $this->input('title')))]);
    }

    public function rules(): array
    {
        return [
            'confirm' => ['accepted'],
            'title' => ['nullable', 'string', 'max:160'],
        ];
    }
}
