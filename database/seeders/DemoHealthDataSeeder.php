<?php

namespace Database\Seeders;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\HealthGoal;
use App\Enums\JournalEntryType;
use App\Enums\Mood;
use App\Models\User;
use App\Services\BMI\BmiRecordService;
use Illuminate\Database\Seeder;

class DemoHealthDataSeeder extends Seeder
{
    public function run(BmiRecordService $bmiService): void
    {
        $user = User::query()->where('email', 'demo@healo.test')->firstOrFail();

        $user->healthProfile()->updateOrCreate([], [
            'height_cm' => 165,
            'weight_kg' => 60,
            'birth_date' => '1995-05-14',
            'gender' => Gender::Female,
            'activity_level' => ActivityLevel::Moderate,
            'health_goal' => HealthGoal::GeneralWellness,
            'health_conditions' => ['Tidak ada kondisi khusus'],
            'allergies' => ['Kacang tanah'],
            'consented_at' => now(),
        ]);

        $user->bmiRecords()->delete();
        foreach ([
            ['weight_kg' => 62, 'height_cm' => 165, 'measured_at' => now()->subDays(60)],
            ['weight_kg' => 61, 'height_cm' => 165, 'measured_at' => now()->subDays(30)],
            ['weight_kg' => 60, 'height_cm' => 165, 'measured_at' => now()->startOfDay()],
        ] as $measurement) {
            $bmiService->record($user, $measurement);
        }

        $journal = $user->healthJournals()->withTrashed()
            ->where('title', 'Kondisi hari ini')
            ->whereDate('journal_date', now()->toDateString())
            ->first() ?? $user->healthJournals()->make([
                'title' => 'Kondisi hari ini',
                'journal_date' => now()->toDateString(),
            ]);
        $journal->fill(['mood' => Mood::Good]);
        $journal->deleted_at = null;
        $journal->save();
        $journal->entries()->delete();
        $journal->entries()->createMany([
            ['type' => JournalEntryType::Complaint, 'content' => 'Sedikit lelah setelah aktivitas pagi.', 'sort_order' => 0],
            ['type' => JournalEntryType::Symptom, 'content' => 'Lelah ringan', 'sort_order' => 1],
            ['type' => JournalEntryType::Note, 'content' => 'Tidur cukup dan tetap minum air sepanjang hari.', 'sort_order' => 2],
        ]);
    }
}
