<?php

namespace App\Models;

use App\Enums\NutritionSource;
use Database\Factories\MealItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealItem extends Model
{
    /** @use HasFactory<MealItemFactory> */
    use HasFactory;

    protected $fillable = [
        'food_name', 'serving_size', 'serving_unit', 'calories',
        'protein_g', 'carbohydrates_g', 'fat_g', 'source', 'confidence',
    ];

    protected function casts(): array
    {
        return [
            'serving_size' => 'decimal:2',
            'calories' => 'decimal:2',
            'protein_g' => 'decimal:2',
            'carbohydrates_g' => 'decimal:2',
            'fat_g' => 'decimal:2',
            'source' => NutritionSource::class,
            'confidence' => 'decimal:3',
        ];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}
