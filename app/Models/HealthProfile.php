<?php

namespace App\Models;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\HealthGoal;
use Database\Factories\HealthProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthProfile extends Model
{
    /** @use HasFactory<HealthProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'height_cm', 'weight_kg', 'birth_date', 'gender', 'activity_level',
        'health_goal', 'health_conditions', 'allergies', 'consented_at',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'birth_date' => 'date',
            'gender' => Gender::class,
            'activity_level' => ActivityLevel::class,
            'health_goal' => HealthGoal::class,
            'health_conditions' => 'encrypted:array',
            'allergies' => 'encrypted:array',
            'consented_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

