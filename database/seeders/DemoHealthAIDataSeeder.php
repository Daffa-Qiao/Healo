<?php

namespace Database\Seeders;

use App\Enums\AIConfidence;
use App\Enums\HealthMessageRole;
use App\Enums\HealthMessageType;
use App\Enums\SymptomUrgency;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoHealthAIDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'demo@healo.test')->firstOrFail();

        $user->healthConversations()->delete();
        $user->symptomAssessments()->delete();

        $conversation = $user->healthConversations()->create([
            'title' => 'Memahami catatan BMI (data demo)',
            'last_message_at' => now(),
        ]);
        $conversation->messages()->createMany([
            [
                'role' => HealthMessageRole::User,
                'message_type' => HealthMessageType::Chat,
                'content' => 'Apa arti catatan BMI saya?',
            ],
            [
                'role' => HealthMessageRole::Assistant,
                'message_type' => HealthMessageType::Chat,
                'content' => 'BMI adalah indikator penyaringan, bukan diagnosis. Nilainya sebaiknya dipahami bersama konteks kesehatan lain.',
                'metadata' => [
                    'provider' => 'local_demo',
                    'model' => 'deterministic_rules_v1',
                    'confidence' => AIConfidence::Low->value,
                    'simulation' => true,
                ],
            ],
        ]);

        $user->symptomAssessments()->create([
            'health_conversation_id' => $conversation->id,
            'input' => 'Saya merasa sakit kepala ringan setelah kurang minum.',
            'summary' => 'Data demo keluhan sakit kepala ringan tanpa tanda bahaya.',
            'urgency' => SymptomUrgency::Routine,
            'red_flags' => [],
            'symptoms' => [['name' => 'headache', 'severity' => 'mild']],
            'possible_categories' => ['hydration or tension-related symptoms'],
            'recommendation' => 'Pantau gejala dan pertimbangkan saran medis bila menetap atau memburuk.',
            'confidence' => AIConfidence::Low,
            'provider' => 'local_demo',
            'model' => 'deterministic_rules_v1',
        ]);
    }
}
