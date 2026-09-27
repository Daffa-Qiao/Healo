<?php

namespace App\Models;

use App\Enums\JournalEntryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthJournalEntry extends Model
{
    protected $fillable = ['type', 'content', 'sort_order'];

    protected function casts(): array
    {
        return [
            'type' => JournalEntryType::class,
            'content' => 'encrypted',
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(HealthJournal::class, 'health_journal_id');
    }
}
