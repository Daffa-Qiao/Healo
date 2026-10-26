<?php

namespace App\Data;

class HealthPlanPersonalization
{
    public function __construct(
        public readonly string $summary,
        public readonly string $suggestion,
        public readonly string $motivation,
        public readonly string $disclaimer,
        public readonly string $provider,
        public readonly string $model,
        public readonly bool $fallback = false,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
