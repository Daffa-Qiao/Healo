<?php

namespace App\Models;

use App\Enums\DietPreference;
use App\Enums\MealType;
use Illuminate\Database\Eloquent\Model;

class HealthyMenu extends Model
{
    protected $fillable = [
        'name', 'slug', 'meal_type', 'diet_preference', 'calories',
        'protein_g', 'carbohydrates_g', 'fat_g', 'ingredients',
        'preparation', 'source', 'attribution', 'active',
    ];

    protected function casts(): array
    {
        return [
            'meal_type' => MealType::class,
            'diet_preference' => DietPreference::class,
            'calories' => 'decimal:2',
            'protein_g' => 'decimal:2',
            'carbohydrates_g' => 'decimal:2',
            'fat_g' => 'decimal:2',
            'ingredients' => 'array',
            'active' => 'boolean',
        ];
    }
}
