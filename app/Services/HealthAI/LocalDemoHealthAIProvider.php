<?php

namespace App\Services\HealthAI;

use App\Contracts\HealthAIProviderInterface;

class LocalDemoHealthAIProvider implements HealthAIProviderInterface
{
    public function respond(array $request): array
    {
        $message = mb_strtolower((string) ($request['user_message'] ?? ''));
        $context = is_array($request['context'] ?? null) ? $request['context'] : [];

        if (str_contains($message, 'bmi') && isset($context['latest_bmi'])) {
            $bmi = $context['latest_bmi'];
            $content = __('health_ai.demo_bmi_response', [
                'value' => number_format((float) $bmi['value'], 1),
                'category' => (string) $bmi['category'],
            ]);
        } elseif ($this->contains($message, ['makan', 'meal', 'nutrition', 'nutrisi', 'kalori']) && isset($context['nutrition_today'])) {
            $nutrition = $context['nutrition_today'];
            $content = __('health_ai.demo_nutrition_response', [
                'calories' => number_format((float) $nutrition['calories_consumed']),
                'target' => number_format((float) $nutrition['calorie_target']),
                'meals' => (int) $nutrition['meals_logged'],
            ]);
        } else {
            $content = __('health_ai.demo_general_response');
        }

        return [
            'content' => $content,
            'provider' => 'local_demo',
            'model' => 'deterministic_rules_v1',
            'confidence' => 'low',
            'metadata' => ['simulation' => true],
        ];
    }

    public function assess(array $request): array
    {
        $input = mb_strtolower((string) ($request['user_message'] ?? ''));
        $symptoms = [];
        $categories = [];

        $map = [
            'demam' => ['fever', 'moderate', 'infection-related symptoms'],
            'fever' => ['fever', 'moderate', 'infection-related symptoms'],
            'sakit kepala' => ['headache', 'mild', 'hydration or tension-related symptoms'],
            'headache' => ['headache', 'mild', 'hydration or tension-related symptoms'],
            'lemas' => ['fatigue', 'mild', 'fatigue or hydration-related symptoms'],
            'fatigue' => ['fatigue', 'mild', 'fatigue or hydration-related symptoms'],
            'batuk' => ['cough', 'mild', 'respiratory irritation'],
            'cough' => ['cough', 'mild', 'respiratory irritation'],
            'mual' => ['nausea', 'mild', 'digestive irritation'],
            'nausea' => ['nausea', 'mild', 'digestive irritation'],
        ];

        foreach ($map as $needle => [$name, $severity, $category]) {
            if (str_contains($input, $needle) && ! collect($symptoms)->contains('name', $name)) {
                $symptoms[] = ['name' => $name, 'severity' => $severity];
                $categories[] = $category;
            }
        }

        if ($symptoms === []) {
            $symptoms[] = ['name' => mb_substr(trim($input), 0, 120), 'severity' => 'mild'];
            $categories[] = 'general health concern';
        }

        $urgency = $this->contains($input, ['lebih dari seminggu', 'worsening', 'memburuk', 'terus menerus']) ? 'soon' : 'routine';
        $differential = collect(array_values(array_unique($categories)))
            ->take(3)
            ->values()
            ->map(fn (string $condition, int $index): array => [
                'rank' => $index + 1,
                'condition' => $condition,
                'specialty' => __('health_ai.general_practitioner'),
                'probability' => max(0.2, 0.55 - ($index * 0.15)),
            ])->all();

        return [
            'summary' => __('health_ai.demo_assessment_summary'),
            'symptoms' => array_slice($symptoms, 0, 20),
            'possible_categories' => array_values(array_unique($categories)),
            'differential' => $differential,
            'recommended_specialty' => __('health_ai.general_practitioner'),
            'referral_needed' => $urgency !== 'routine',
            'red_flags' => [],
            'urgency' => $urgency,
            'recommendation' => $urgency === 'soon'
                ? __('health_ai.demo_soon_recommendation')
                : __('health_ai.demo_routine_recommendation'),
            'confidence' => 'low',
            'provider' => 'local_demo',
            'model' => 'deterministic_rules_v1',
        ];
    }

    public function insights(array $request): array
    {
        $context = is_array($request['context'] ?? null) ? $request['context'] : [];
        $loggedDays = (int) data_get($context, 'nutrition.logged_days', 0);

        return [
            'summary' => __('insights.local_ai_summary'),
            'insights' => [[
                'type' => $loggedDays > 0 ? 'nutrition' : 'general',
                'title' => __('insights.local_ai_title'),
                'description' => $loggedDays > 0
                    ? __('insights.rule_nutrition_logged', ['days' => $loggedDays])
                    : __('insights.rule_getting_started'),
                'severity' => $loggedDays > 0 ? 'positive' : 'info',
            ]],
            'recommendations' => [[
                'category' => 'general',
                'text' => __('insights.local_ai_recommendation'),
            ]],
            'disclaimer' => __('insights.ai_disclaimer'),
        ];
    }

    public function wellness(array $request): array
    {
        $percentage = (int) data_get($request, 'context.plan.progress_percentage', 0);

        return [
            'summary' => __('wellness.local_ai_summary', ['percentage' => $percentage]),
            'suggestion' => __('wellness.local_ai_suggestion'),
            'motivation' => __('wellness.local_ai_motivation'),
            'disclaimer' => __('wellness.ai_disclaimer'),
        ];
    }

    /** @param list<string> $needles */
    private function contains(string $text, array $needles): bool
    {
        return collect($needles)->contains(fn (string $needle): bool => str_contains($text, $needle));
    }
}
