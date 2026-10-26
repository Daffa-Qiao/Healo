<?php

namespace App\Models;

use Database\Factories\HealthConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthConversation extends Model
{
    /** @use HasFactory<HealthConversationFactory> */
    use HasFactory;

    protected $fillable = ['title', 'last_message_at'];

    protected function casts(): array
    {
        return [
            'title' => 'encrypted',
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(HealthMessage::class)->oldest('id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(SymptomAssessment::class);
    }
}
