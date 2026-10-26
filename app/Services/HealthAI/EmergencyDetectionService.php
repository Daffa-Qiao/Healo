<?php

namespace App\Services\HealthAI;

class EmergencyDetectionService
{
    /** @var array<string, list<string>> */
    private array $patterns = [
        'severe_chest_pain' => ['severe chest pain', 'chest pain severe', 'nyeri dada hebat', 'sakit dada hebat'],
        'severe_difficulty_breathing' => ['severe difficulty breathing', 'cannot breathe', "can't breathe", 'sesak napas berat', 'tidak bisa bernapas'],
        'loss_of_consciousness' => ['loss of consciousness', 'unconscious', 'tidak sadarkan diri', 'pingsan dan tidak sadar'],
        'stroke_like_symptoms' => ['face drooping', 'slurred speech', 'one-sided weakness', 'wajah mencong', 'bicara pelo', 'lemah satu sisi'],
        'severe_bleeding' => ['severe bleeding', 'uncontrolled bleeding', 'pendarahan hebat', 'perdarahan hebat', 'darah tidak berhenti'],
        'severe_allergic_reaction' => ['anaphylaxis', 'throat swelling', 'severe allergic reaction', 'tenggorokan membengkak', 'alergi berat'],
        'self_harm_emergency' => ['want to kill myself', 'suicidal', 'hurt myself', 'self-harm', 'self harm', 'bunuh diri', 'menyakiti diri', 'mengakhiri hidup'],
    ];

    /** @return list<string> */
    public function detect(string $input): array
    {
        $normalized = mb_strtolower($input);
        $matches = [];

        foreach ($this->patterns as $flag => $phrases) {
            foreach ($phrases as $phrase) {
                if (str_contains($normalized, $phrase)) {
                    $matches[] = $flag;
                    break;
                }
            }
        }

        return $matches;
    }
}
