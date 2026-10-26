<?php

namespace Database\Factories;

use App\Enums\AIConfidence;
use App\Enums\SymptomUrgency;
use App\Models\SymptomAssessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SymptomAssessment> */
class SymptomAssessmentFactory extends Factory
{
    protected $model = SymptomAssessment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'input' => 'Saya merasa sakit kepala ringan sejak pagi.',
            'summary' => 'Keluhan sakit kepala ringan sejak pagi.',
            'urgency' => SymptomUrgency::Routine,
            'red_flags' => [],
            'symptoms' => [['name' => 'sakit kepala', 'severity' => 'mild']],
            'possible_categories' => ['dehydration', 'tension-related discomfort'],
            'differential' => [
                ['rank' => 1, 'condition' => 'dehydration', 'specialty' => 'Dokter Umum', 'probability' => 0.55],
                ['rank' => 2, 'condition' => 'tension-related discomfort', 'specialty' => 'Dokter Umum', 'probability' => 0.35],
            ],
            'recommended_specialty' => 'Dokter Umum',
            'referral_needed' => false,
            'recommendation' => 'Pantau gejala dan pertimbangkan berkonsultasi jika memburuk.',
            'confidence' => AIConfidence::Low,
            'provider' => 'local_demo',
            'model' => 'deterministic_rules_v1',
        ];
    }
}
