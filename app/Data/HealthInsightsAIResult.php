<?php

namespace App\Data;

final readonly class HealthInsightsAIResult
{
    /**
     * @param  list<array{type: string, title: string, description: string, severity: string}>  $insights
     * @param  list<array{category: string, text: string}>  $recommendations
     */
    public function __construct(
        public string $summary,
        public array $insights,
        public array $recommendations,
        public string $disclaimer,
        public string $provider,
        public string $model,
        public bool $fallback = false,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'insights' => $this->insights,
            'recommendations' => $this->recommendations,
            'disclaimer' => $this->disclaimer,
            'provider' => $this->provider,
            'model' => $this->model,
            'fallback' => $this->fallback,
        ];
    }
}
