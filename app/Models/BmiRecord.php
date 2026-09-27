<?php

namespace App\Models;

use App\Enums\BmiCategory;
use Database\Factories\BmiRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BmiRecord extends Model
{
    /** @use HasFactory<BmiRecordFactory> */
    use HasFactory;

    protected $fillable = ['height_cm', 'weight_kg', 'bmi', 'category', 'measured_at'];

    protected function casts(): array
    {
        return [
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'bmi' => 'decimal:2',
            'category' => BmiCategory::class,
            'measured_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

