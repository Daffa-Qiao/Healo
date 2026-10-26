<?php

namespace App\Models;

use App\Enums\HealthGoalStatus;
use App\Enums\HealthGoalType;
use Database\Factories\HealthGoalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthGoal extends Model
{
    /** @use HasFactory<HealthGoalFactory> */
    use HasFactory;

    protected $fillable = [
        'type', 'target_value', 'current_value', 'unit', 'start_date',
        'target_date', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => HealthGoalType::class,
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'start_date' => 'date',
            'target_date' => 'date',
            'status' => HealthGoalStatus::class,
            'metadata' => 'encrypted:array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
