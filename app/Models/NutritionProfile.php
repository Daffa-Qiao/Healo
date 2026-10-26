<?php

namespace App\Models;

use App\Enums\ActivityLevel;
use App\Enums\DietPreference;
use App\Enums\NutritionGoal;
use Database\Factories\NutritionProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionProfile extends Model
{
    /** @use HasFactory<NutritionProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'daily_calorie_target', 'activity_level', 'goal', 'diet_preference',
        'food_preferences', 'target_source', 'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'daily_calorie_target' => 'integer',
            'activity_level' => ActivityLevel::class,
            'goal' => NutritionGoal::class,
            'diet_preference' => DietPreference::class,
            'food_preferences' => 'encrypted:array',
            'calculated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
