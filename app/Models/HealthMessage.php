<?php

namespace App\Models;

use App\Enums\HealthMessageRole;
use App\Enums\HealthMessageType;
use Database\Factories\HealthMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthMessage extends Model
{
    /** @use HasFactory<HealthMessageFactory> */
    use HasFactory;

    protected $fillable = ['role', 'message_type', 'content', 'metadata'];

    protected $hidden = ['metadata'];

    protected function casts(): array
    {
        return [
            'role' => HealthMessageRole::class,
            'message_type' => HealthMessageType::class,
            'content' => 'encrypted',
            'metadata' => 'encrypted:array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(HealthConversation::class, 'health_conversation_id');
    }
}
