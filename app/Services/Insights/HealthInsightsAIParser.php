<?php

namespace App\Services\Insights;

use App\Data\HealthInsightsAIResult;
use App\Enums\HealthInsightCategory;
use App\Enums\HealthInsightSeverity;
use App\Exceptions\HealthAIException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class HealthInsightsAIParser
{
    /** @param array<string, mixed> $payload */
    public function parse(array $payload, string $provider, string $model): HealthInsightsAIResult
    {
        try {
            $data = Validator::make($payload, [
                'summary' => ['required', 'string', 'max:1200'],
                'insights' => ['present', 'array', 'max:8'],
                'insights.*.type' => ['required', Rule::enum(HealthInsightCategory::class)],
                'insights.*.title' => ['required', 'string', 'max:160'],
                'insights.*.description' => ['required', 'string', 'max:1000'],
                'insights.*.severity' => ['required', Rule::enum(HealthInsightSeverity::class)],
                'recommendations' => ['present', 'array', 'max:8'],
                'recommendations.*.category' => ['required', Rule::enum(HealthInsightCategory::class)],
                'recommendations.*.text' => ['required', 'string', 'max:1000'],
                'disclaimer' => ['required', 'string', 'max:1000'],
            ])->validate();
        } catch (Throwable $exception) {
            throw new HealthAIException('invalid_health_insights_response', $exception);
        }

        return new HealthInsightsAIResult(
            $data['summary'],
            array_values($data['insights']),
            array_values($data['recommendations']),
            $data['disclaimer'],
            $provider,
            $model,
        );
    }
}
