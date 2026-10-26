<?php

namespace App\Models;

use App\Enums\JournalEntryType;
use App\Enums\Mood;
use Database\Factories\HealthJournalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthJournal extends Model
{
    /** @use HasFactory<HealthJournalFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'journal_date',
        'mood',
        'weight_kg',
        'systolic',
        'diastolic',
        'sleep_hours',
        'water_liters',
        'exercise_minutes',
    ];

    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
            'mood' => Mood::class,
            'weight_kg' => 'decimal:2',
            'systolic' => 'integer',
            'diastolic' => 'integer',
            'sleep_hours' => 'decimal:2',
            'water_liters' => 'decimal:2',
            'exercise_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(HealthJournalEntry::class)->orderBy('sort_order');
    }

    public function entryContent(string $type): ?string
    {
        return $this->entries->first(
            fn (HealthJournalEntry $entry): bool => $entry->type->value === $type
        )?->content;
    }

    /** @return list<string> */
    public function symptomList(): array
    {
        return $this->entries
            ->filter(fn (HealthJournalEntry $entry): bool => $entry->type === JournalEntryType::Symptom)
            ->pluck('content')
            ->values()
            ->all();
    }
}
