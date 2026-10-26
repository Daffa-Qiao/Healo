<?php

namespace App\Models;

use App\Enums\FoodAnalysisStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodAnalysis extends Model
{
    protected $fillable = [
        'image_path', 'status', 'result', 'confidence', 'provider',
        'failure_code', 'analyzed_at', 'confirmed_at', 'expires_at',
    ];

    protected $hidden = ['image_path', 'failure_code'];

    protected function casts(): array
    {
        return [
            'image_path' => 'encrypted',
            'status' => FoodAnalysisStatus::class,
            'result' => 'encrypted:array',
            'confidence' => 'decimal:3',
            'failure_code' => 'encrypted',
            'analyzed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
