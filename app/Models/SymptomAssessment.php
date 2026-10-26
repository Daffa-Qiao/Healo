<?php

namespace App\Models;

use App\Enums\AIConfidence;
use App\Enums\SymptomUrgency;
use Database\Factories\SymptomAssessmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymptomAssessment extends Model
{
    /** @use HasFactory<SymptomAssessmentFactory> */
    use HasFactory;

    protected $fillable = [
        'health_conversation_id', 'health_journal_id', 'input', 'summary', 'urgency',
        'red_flags', 'symptoms', 'possible_categories', 'recommendation',
        'differential', 'recommended_specialty', 'referral_needed',
        'confidence', 'provider', 'model', 'saved_to_journal_at',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'encrypted',
            'summary' => 'encrypted',
            'urgency' => SymptomUrgency::class,
            'red_flags' => 'encrypted:array',
            'symptoms' => 'encrypted:array',
            'possible_categories' => 'encrypted:array',
            'differential' => 'encrypted:array',
            'recommended_specialty' => 'encrypted',
            'referral_needed' => 'boolean',
            'recommendation' => 'encrypted',
            'confidence' => AIConfidence::class,
            'saved_to_journal_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(HealthConversation::class, 'health_conversation_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(HealthJournal::class, 'health_journal_id');
    }
}
