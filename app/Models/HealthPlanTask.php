<?php

namespace App\Models;

use App\Enums\HealthPlanTaskCompletionMode;
use App\Enums\HealthPlanTaskSource;
use App\Enums\HealthPlanTaskStatus;
use App\Enums\HealthPlanTaskType;
use Database\Factories\HealthPlanTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthPlanTask extends Model
{
    /** @use HasFactory<HealthPlanTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'type', 'title', 'description', 'status', 'priority', 'source',
        'source_reference', 'completion_mode', 'completed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => HealthPlanTaskType::class,
            'status' => HealthPlanTaskStatus::class,
            'source' => HealthPlanTaskSource::class,
            'completion_mode' => HealthPlanTaskCompletionMode::class,
            'completed_at' => 'datetime',
            'metadata' => 'encrypted:array',
        ];
    }

    public function healthPlan(): BelongsTo
    {
        return $this->belongsTo(HealthPlan::class);
    }
}
