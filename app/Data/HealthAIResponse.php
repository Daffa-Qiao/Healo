<?php

namespace App\Data;

use App\Enums\AIConfidence;

final readonly class HealthAIResponse
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $content,
        public string $provider,
        public string $model,
        public AIConfidence $confidence,
        public array $metadata = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'provider' => $this->provider,
            'model' => $this->model,
            'confidence' => $this->confidence->value,
            'metadata' => $this->metadata,
        ];
    }
}
