<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionDailySummary extends Model
{
    protected $fillable = [
        'summary_date', 'calorie_target', 'calories_consumed',
        'calories_remaining', 'meals_logged',
    ];

    protected function casts(): array
    {
        return [
            'summary_date' => 'date',
            'calorie_target' => 'integer',
            'calories_consumed' => 'decimal:2',
            'calories_remaining' => 'decimal:2',
            'meals_logged' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
