<?php

namespace App\Exceptions;

use RuntimeException;

class HealthAIException extends RuntimeException
{
    public function __construct(public readonly string $errorCode = 'health_ai_unavailable', ?\Throwable $previous = null)
    {
        parent::__construct($errorCode, previous: $previous);
    }
}
