<?php

namespace App\Models;

use App\Enums\MealType;
use Database\Factories\MealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meal extends Model
{
    /** @use HasFactory<MealFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['meal_date', 'meal_type', 'notes', 'total_calories'];

    protected function casts(): array
    {
        return [
            'meal_date' => 'date',
            'meal_type' => MealType::class,
            'notes' => 'encrypted',
            'total_calories' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MealItem::class)->orderBy('id');
    }
}
