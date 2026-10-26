<?php

namespace App\Services\Journal;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class HealthJournalDashboardService
{
    /** @return array<string, mixed> */
    public function build(User $user, int $period = 14): array
    {
        $period = max(7, min($period, 30));
        $start = CarbonImmutable::today()->subDays($period - 1);
        $journals = $user->healthJournals()
            ->whereDate('journal_date', '>=', $start)
            ->oldest('journal_date')
            ->oldest('created_at')
            ->get();
        $bmiRecords = $user->bmiRecords()
            ->where('measured_at', '>=', $start->startOfDay())
            ->oldest('measured_at')
            ->get();

        $journalsByDate = $journals->groupBy(fn ($journal): string => $journal->journal_date->toDateString());
        $bmiByDate = $bmiRecords->groupBy(fn ($record): string => $record->measured_at->toDateString());
        $dates = $journalsByDate->keys()->merge($bmiByDate->keys())->unique()->sort()->values();

        $rows = $dates->map(function (string $date) use ($journalsByDate, $bmiByDate): array {
            $dailyJournals = $journalsByDate->get($date, collect());
            $dailyBmi = $bmiByDate->get($date, collect())->last();

            return [
                'date' => $date,
                'label' => CarbonImmutable::parse($date)->translatedFormat('d M'),
                'weight' => $this->latestValue($dailyJournals, 'weight_kg') ?? ($dailyBmi ? (float) $dailyBmi->weight_kg : null),
                'systolic' => $this->latestValue($dailyJournals, 'systolic'),
                'diastolic' => $this->latestValue($dailyJournals, 'diastolic'),
                'sleep' => $this->latestValue($dailyJournals, 'sleep_hours'),
                'water' => $this->latestValue($dailyJournals, 'water_liters'),
                'exercise' => $this->latestValue($dailyJournals, 'exercise_minutes'),
                'mood' => $this->latestMoodScore($dailyJournals),
            ];
        });

        $recentRows = $rows->filter(fn (array $row): bool => $row['date'] >= CarbonImmutable::today()->subDays(6)->toDateString());
        $latest = [
            'weight' => $this->latestRowValue($rows, 'weight'),
            'systolic' => $this->latestRowValue($rows, 'systolic'),
            'diastolic' => $this->latestRowValue($rows, 'diastolic'),
        ];
        $averages = [
            'sleep' => $this->average($recentRows, 'sleep'),
            'water' => $this->average($recentRows, 'water'),
            'exercise' => $this->average($recentRows, 'exercise'),
            'mood' => $this->average($recentRows, 'mood'),
        ];

        $highlights = $this->highlights($user, $rows, $latest, $averages);
        $scored = collect($highlights)->whereNotNull('score');
        $score = $scored->isEmpty() ? null : (int) round($scored->avg('score'));
        $achievement = $scored->sortByDesc('score')->first();
        $concern = $scored->sortBy('score')->first();
        $needsAttention = $concern && $concern['score'] < 85;

        return [
            'period_days' => $period,
            'days_analyzed' => $journals->pluck('journal_date')->map->toDateString()->unique()->count(),
            'days_recorded' => $user->healthJournals()->select('journal_date')->distinct()->count('journal_date'),
            'latest' => $latest,
            'averages' => $averages,
            'score' => $score,
            'score_label' => $score === null ? __('journal.score_not_ready') : $this->scoreLabel($score),
            'highlights' => $highlights,
            'top_achievement' => $achievement
                ? __('journal.achievement_format', ['metric' => $achievement['label'], 'insight' => $achievement['insight']])
                : __('journal.insufficient_summary'),
            'main_concern' => $needsAttention
                ? __('journal.concern_format', ['metric' => $concern['label'], 'insight' => $concern['insight']])
                : __('journal.no_main_concern'),
            'action_items' => $this->actionItems($highlights),
            'charts' => [
                'labels' => $rows->pluck('label')->all(),
                'weight' => $rows->pluck('weight')->all(),
                'systolic' => $rows->pluck('systolic')->all(),
                'diastolic' => $rows->pluck('diastolic')->all(),
                'sleep' => $rows->pluck('sleep')->all(),
                'water' => $rows->pluck('water')->all(),
                'exercise' => $rows->pluck('exercise')->all(),
                'mood' => $rows->pluck('mood')->all(),
            ],
        ];
    }

    private function latestValue(Collection $items, string $field): int|float|null
    {
        $value = $items->reverse()->first(fn ($item): bool => $item->{$field} !== null)?->{$field};

        return $value !== null ? (float) $value : null;
    }

    private function latestMoodScore(Collection $items): ?int
    {
        $mood = $items->reverse()->first(fn ($item): bool => $item->mood !== null)?->mood?->value;

        return match ($mood) {
            'very_good' => 5,
            'good' => 4,
            'neutral' => 3,
            'low' => 2,
            'very_low' => 1,
            default => null,
        };
    }

    private function latestRowValue(Collection $rows, string $field): int|float|null
    {
        return $rows->reverse()->first(fn (array $row): bool => $row[$field] !== null)[$field] ?? null;
    }

    private function average(Collection $rows, string $field): ?float
    {
        $values = $rows->pluck($field)->filter(fn ($value): bool => $value !== null);

        return $values->isEmpty() ? null : round((float) $values->avg(), 1);
    }

    /**
     * @param  array<string, int|float|null>  $latest
     * @param  array<string, float|null>  $averages
     * @return list<array{key: string, label: string, status: string, insight: string, score: int|null}>
     */
    private function highlights(User $user, Collection $rows, array $latest, array $averages): array
    {
        $highlights = [];
        $profile = $user->healthProfile()->first(['height_cm', 'birth_date']);
        $height = (float) ($profile?->height_cm ?? 0);
        $age = $profile?->birth_date?->age;

        if ($latest['weight'] !== null) {
            $weights = $rows->pluck('weight')->filter(fn ($value): bool => $value !== null)->values();
            $change = $weights->count() >= 2 ? round((float) $weights->last() - (float) $weights->first(), 1) : null;
            $bmi = $height > 0 && $age !== null && $age >= 20
                ? round((float) $latest['weight'] / (($height / 100) ** 2), 1)
                : null;
            $weightScore = $bmi === null ? null : $this->bmiScore($bmi);
            $highlights[] = $this->highlight(
                'weight',
                $weightScore,
                $change === null
                    ? __('journal.insight_weight_latest', ['value' => number_format((float) $latest['weight'], 1)])
                    : __('journal.insight_weight_change', ['change' => ($change > 0 ? '+' : '').number_format($change, 1)]),
            );
        }

        if ($latest['systolic'] !== null && $latest['diastolic'] !== null) {
            $bpScore = $age !== null && $age >= 18
                ? $this->bloodPressureScore((int) $latest['systolic'], (int) $latest['diastolic'])
                : null;
            $highlights[] = $this->highlight('blood_pressure', $bpScore, __('journal.insight_blood_pressure', [
                'systolic' => (int) $latest['systolic'],
                'diastolic' => (int) $latest['diastolic'],
            ]));
        }

        if ($averages['sleep'] !== null) {
            $highlights[] = $this->highlight('sleep', $this->sleepScore($averages['sleep']), __('journal.insight_sleep', ['value' => number_format($averages['sleep'], 1)]));
        }

        if ($averages['water'] !== null) {
            $highlights[] = $this->highlight('water', $this->waterScore($averages['water']), __('journal.insight_water', ['value' => number_format($averages['water'], 1)]));
        }

        if ($averages['exercise'] !== null) {
            $highlights[] = $this->highlight('exercise', $this->exerciseScore($averages['exercise']), __('journal.insight_exercise', ['value' => number_format($averages['exercise'], 0)]));
        }

        if ($averages['mood'] !== null) {
            $highlights[] = $this->highlight('mood', $this->moodScore($averages['mood']), __('journal.insight_mood', ['value' => number_format($averages['mood'], 1)]));
        }

        return $highlights;
    }

    /** @return array{key: string, label: string, status: string, insight: string, score: int|null} */
    private function highlight(string $key, ?int $score, string $insight): array
    {
        return [
            'key' => $key,
            'label' => __('journal.metric_'.$key),
            'status' => $score === null ? 'neutral' : ($score >= 85 ? 'good' : 'attention'),
            'insight' => $insight,
            'score' => $score,
        ];
    }

    /** @param list<array{key: string, label: string, status: string, insight: string, score: int|null}> $highlights */
    private function actionItems(array $highlights): array
    {
        $actions = collect($highlights)
            ->where('status', 'attention')
            ->sortBy('score')
            ->map(fn (array $highlight): string => __('journal.action_'.$highlight['key']))
            ->take(3)
            ->values();

        if ($actions->isEmpty()) {
            $actions->push(__('journal.action_maintain'));
        }

        if ($actions->count() < 3) {
            $actions->push(__('journal.action_log'));
        }

        if ($actions->count() < 3) {
            $actions->push(__('journal.action_review'));
        }

        return $actions->unique()->take(3)->values()->all();
    }

    private function bloodPressureScore(int $systolic, int $diastolic): int
    {
        if ($systolic > 180 || $diastolic > 120) {
            return 20;
        }

        if ($systolic >= 140 || $diastolic >= 90) {
            return 45;
        }

        if ($systolic >= 130 || $diastolic >= 80) {
            return 65;
        }

        if (($systolic >= 120 && $systolic <= 129) || $systolic < 90 || $diastolic < 60) {
            return 80;
        }

        return 100;
    }

    private function sleepScore(float $hours): int
    {
        return match (true) {
            $hours >= 7 && $hours <= 9 => 100,
            $hours >= 6 && $hours <= 10 => 80,
            $hours >= 5 && $hours <= 11 => 60,
            default => 35,
        };
    }

    private function waterScore(float $liters): int
    {
        return match (true) {
            $liters >= 1.5 && $liters <= 3.5 => 100,
            $liters >= 1 && $liters <= 5 => 75,
            default => 50,
        };
    }

    private function exerciseScore(float $minutes): int
    {
        return match (true) {
            $minutes >= 30 => 100,
            $minutes >= 20 => 80,
            $minutes >= 10 => 65,
            $minutes > 0 => 45,
            default => 30,
        };
    }

    private function moodScore(float $mood): int
    {
        return match (true) {
            $mood >= 4.5 => 100,
            $mood >= 3.5 => 85,
            $mood >= 2.5 => 70,
            $mood >= 1.5 => 50,
            default => 30,
        };
    }

    private function bmiScore(float $bmi): int
    {
        return match (true) {
            $bmi >= 18.5 && $bmi < 25 => 100,
            $bmi >= 17.5 && $bmi < 30 => 80,
            $bmi >= 16 && $bmi < 35 => 60,
            default => 40,
        };
    }

    private function scoreLabel(int $score): string
    {
        return match (true) {
            $score >= 85 => __('journal.score_very_good'),
            $score >= 70 => __('journal.score_good'),
            $score >= 50 => __('journal.score_monitor'),
            default => __('journal.score_attention'),
        };
    }
}
