<?php

namespace App\Http\Requests;

use App\Enums\Mood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $symptoms = $this->input('symptoms');
        if (is_string($symptoms)) {
            $symptoms = preg_split('/[\r\n,]+/', $symptoms) ?: [];
        }

        $this->merge([
            'title' => trim(strip_tags((string) $this->input('title'))),
            'complaint' => $this->cleanText($this->input('complaint')),
            'notes' => $this->cleanText($this->input('notes')),
            'symptoms' => collect(is_array($symptoms) ? $symptoms : [])
                ->map(fn (mixed $item): string => trim(strip_tags((string) $item)))
                ->filter()->values()->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'journal_date' => ['required', 'date', 'before_or_equal:today'],
            'mood' => ['nullable', Rule::enum(Mood::class)],
            'complaint' => ['nullable', 'string', 'max:3000', 'required_without_all:symptoms,notes'],
            'symptoms' => ['array', 'max:20', 'required_without_all:complaint,notes'],
            'symptoms.*' => ['string', 'max:120', 'distinct:ignore_case'],
            'notes' => ['nullable', 'string', 'max:5000', 'required_without_all:complaint,symptoms'],
        ];
    }

    private function cleanText(mixed $value): ?string
    {
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }
}

