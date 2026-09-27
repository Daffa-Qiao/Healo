<?php

namespace App\Models;

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

    protected $fillable = ['title', 'journal_date', 'mood'];

    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
            'mood' => Mood::class,
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
            ->filter(fn (HealthJournalEntry $entry): bool => $entry->type === \App\Enums\JournalEntryType::Symptom)
            ->pluck('content')
            ->values()
            ->all();
    }
}

