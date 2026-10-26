<?php

namespace App\Data;

use App\Enums\HealthGoalStatus;

final readonly class HealthGoalProgress
{
    public function __construct(
        public ?float $currentValue,
        public float $targetValue,
        public ?float $remainingValue,
        public float $percentage,
        public HealthGoalStatus $status,
        public string $unit,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'current_value' => $this->currentValue,
            'target_value' => $this->targetValue,
            'remaining_value' => $this->remainingValue,
            'percentage' => $this->percentage,
            'status' => $this->status->value,
            'unit' => $this->unit,
        ];
    }
}
