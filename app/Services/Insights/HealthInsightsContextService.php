<?php

namespace App\Services\Insights;

class HealthInsightsContextService
{
    /**
     * Only aggregates and enum-like labels are included. Raw journal text,
     * meal descriptions, identifiers, credentials, and profile PII are excluded.
     *
     * @param  array<string, mixed>  $overview
     * @return array<string, mixed>
     */
    public function fromOverview(array $overview): array
    {
        return [
            'period_days' => $overview['period_days'],
            'bmi' => [
                'latest' => data_get($overview, 'bmi.latest'),
                'category' => data_get($overview, 'bmi.category'),
                'change' => data_get($overview, 'bmi.change'),
                'record_count' => data_get($overview, 'bmi.record_count', 0),
            ],
            'nutrition' => [
                'average_calories' => data_get($overview, 'nutrition.average_calories'),
                'target_calories' => data_get($overview, 'nutrition.target_calories'),
                'average_protein_g' => data_get($overview, 'nutrition.average_protein_g'),
                'average_carbohydrates_g' => data_get($overview, 'nutrition.average_carbohydrates_g'),
                'average_fat_g' => data_get($overview, 'nutrition.average_fat_g'),
                'meal_count' => data_get($overview, 'nutrition.meal_count', 0),
                'logged_days' => data_get($overview, 'nutrition.logged_days', 0),
            ],
            'journal' => [
                'journal_count' => data_get($overview, 'journal.journal_count', 0),
                'most_common_mood' => data_get($overview, 'journal.most_common_mood'),
                'complaint_count' => data_get($overview, 'journal.complaint_count', 0),
                'symptom_count' => data_get($overview, 'journal.symptom_count', 0),
            ],
            'ai_usage' => [
                'conversation_count' => data_get($overview, 'ai.conversation_count', 0),
                'assessment_count' => data_get($overview, 'ai.assessment_count', 0),
            ],
            'goals' => collect($overview['goals'] ?? [])->take(10)->map(fn (array $goal): array => [
                'type' => $goal['type'],
                'target_value' => $goal['target_value'],
                'current_value' => $goal['current_value'],
                'percentage' => $goal['percentage'],
                'status' => $goal['status'],
                'unit' => $goal['unit'],
            ])->values()->all(),
        ];
    }
}
