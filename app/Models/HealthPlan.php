<?php

namespace App\Models;

use App\Enums\HealthPlanStatus;
use Database\Factories\HealthPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthPlan extends Model
{
    /** @use HasFactory<HealthPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'plan_date', 'status', 'progress_percentage', 'generated_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'plan_date' => 'date',
            'status' => HealthPlanStatus::class,
            'progress_percentage' => 'integer',
            'generated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(HealthPlanTask::class)->orderBy('priority')->orderBy('id');
    }
}
