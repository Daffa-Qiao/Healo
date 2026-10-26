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
            'weight_kg' => ['nullable', 'numeric', 'min:10', 'max:350'],
            'systolic' => ['nullable', 'integer', 'min:60', 'max:260', 'required_with:diastolic'],
            'diastolic' => ['nullable', 'integer', 'min:30', 'max:160', 'required_with:systolic'],
            'sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'water_liters' => ['nullable', 'numeric', 'min:0', 'max:15'],
            'exercise_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'complaint' => ['nullable', 'string', 'max:3000', 'required_without_all:symptoms,notes,weight_kg,systolic,diastolic,sleep_hours,water_liters,exercise_minutes'],
            'symptoms' => ['array', 'max:20', 'required_without_all:complaint,notes,weight_kg,systolic,diastolic,sleep_hours,water_liters,exercise_minutes'],
            'symptoms.*' => ['string', 'max:120', 'distinct:ignore_case'],
            'notes' => ['nullable', 'string', 'max:5000', 'required_without_all:complaint,symptoms,weight_kg,systolic,diastolic,sleep_hours,water_liters,exercise_minutes'],
        ];
    }

    private function cleanText(mixed $value): ?string
    {
        $clean = trim(strip_tags((string) $value));

        return $clean === '' ? null : $clean;
    }
}
