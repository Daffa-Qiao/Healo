<?php

namespace App\Services\HealthAI;

use App\Models\User;

class HealthAIContextService
{
    /** @return array<string, mixed> */
    public function forAssistant(User $user, string $message): array
    {
        $text = mb_strtolower($message);
        $context = [];

        if ($this->mentions($text, ['bmi', 'berat', 'weight', 'tinggi', 'height', 'tubuh'])) {
            $this->addProfileAndBmi($context, $user);
        }

        if ($this->mentions($text, ['makan', 'meal', 'food', 'nutrition', 'nutrisi', 'kalori', 'protein'])) {
            $summary = $user->nutritionDailySummaries()->whereDate('summary_date', today())->first();
            if ($summary) {
                $context['nutrition_today'] = [
                    'calorie_target' => $summary->calorie_target,
                    'calories_consumed' => (float) $summary->calories_consumed,
                    'calories_remaining' => (float) $summary->calories_remaining,
                    'meals_logged' => $summary->meals_logged,
                ];
            }
        }

        if ($this->mentions($text, ['gejala', 'symptom', 'sakit', 'pain', 'demam', 'fever', 'jurnal', 'journal'])) {
            $this->addProfileAndBmi($context, $user);
            $context['recent_symptoms'] = $user->healthJournals()
                ->with('entries')->latest('journal_date')
                ->limit((int) config('health_ai.recent_journal_limit', 3))->get()
                ->flatMap(fn ($journal) => $journal->symptomList())
                ->take(10)->values()->all();
        }

        return $context;
    }

    /** @return array<string, mixed> */
    public function forAssessment(User $user): array
    {
        $context = [];
        $profile = $user->healthProfile()->first();

        if ($profile) {
            $context['profile'] = array_filter([
                'age_range' => $profile->birth_date ? $this->ageRange($profile->birth_date->age) : null,
                'gender' => $profile->gender?->value,
            ], fn ($value) => $value !== null);
            $context['known_conditions'] = array_slice($profile->health_conditions ?? [], 0, 10);
            $context['allergies'] = array_slice($profile->allergies ?? [], 0, 10);
        }

        $context['recent_symptoms'] = $user->healthJournals()
            ->with('entries')->latest('journal_date')
            ->limit((int) config('health_ai.recent_journal_limit', 3))->get()
            ->flatMap(fn ($journal) => $journal->symptomList())
            ->take(10)->values()->all();

        return array_filter($context, fn ($value) => $value !== null && $value !== []);
    }

    /** @param array<string, mixed> $context */
    private function addProfileAndBmi(array &$context, User $user): void
    {
        $profile = $user->healthProfile()->first();
        if ($profile) {
            $context['profile'] = array_filter([
                'age_range' => $profile->birth_date ? $this->ageRange($profile->birth_date->age) : null,
                'gender' => $profile->gender?->value,
                'height_cm' => $profile->height_cm ? (float) $profile->height_cm : null,
                'weight_kg' => $profile->weight_kg ? (float) $profile->weight_kg : null,
            ], fn ($value) => $value !== null);
        }

        $bmi = $user->bmiRecords()->latest('measured_at')->first();
        if ($bmi) {
            $context['latest_bmi'] = [
                'value' => (float) $bmi->bmi,
                'category' => $bmi->category->value,
                'measured_at' => $bmi->measured_at->toDateString(),
            ];
        }
    }

    private function ageRange(int $age): string
    {
        $lower = intdiv(max(0, $age), 10) * 10;

        return $lower >= 100 ? '100+' : $lower.'-'.($lower + 9);
    }

    /** @param list<string> $needles */
    private function mentions(string $text, array $needles): bool
    {
        return collect($needles)->contains(fn (string $needle): bool => str_contains($text, $needle));
    }
}
