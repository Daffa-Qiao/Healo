<?php

namespace App\Contracts;

interface HealthAIProviderInterface
{
    /**
     * The request keeps system instructions, minimized context, history, and user
     * input in separate fields so user content is never treated as instructions.
     *
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>
     */
    public function respond(array $request): array;

    /**
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>
     */
    public function assess(array $request): array;

    /**
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>
     */
    public function insights(array $request): array;

    /**
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>
     */
    public function wellness(array $request): array;
}
