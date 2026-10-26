<?php

namespace App\Http\Requests;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\HealthGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'health_conditions' => $this->normalizeList($this->input('health_conditions')),
            'allergies' => $this->normalizeList($this->input('allergies')),
        ]);
    }

    public function rules(): array
    {
        $consentRules = $this->user()->healthProfile()->exists()
            ? ['sometimes', 'accepted']
            : ['required', 'accepted'];

        return [
            'height_cm' => ['nullable', 'numeric', 'between:80,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:20,400'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'activity_level' => ['nullable', Rule::enum(ActivityLevel::class)],
            'health_goal' => ['nullable', Rule::enum(HealthGoal::class)],
            'health_conditions' => ['array', 'max:20'],
            'health_conditions.*' => ['string', 'max:120', 'distinct:ignore_case'],
            'allergies' => ['array', 'max:20'],
            'allergies.*' => ['string', 'max:120', 'distinct:ignore_case'],
            'consent' => $consentRules,
        ];
    }

    /** @return list<string> */
    private function normalizeList(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        return collect(is_array($value) ? $value : [])
            ->map(fn (mixed $item): string => trim(strip_tags((string) $item)))
            ->filter()
            ->values()
            ->all();
    }
}
