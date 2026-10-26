<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'message_type' => $this->message_type->value,
            'content' => $this->content,
            'provider' => $this->role->value === 'assistant' ? data_get($this->metadata, 'provider') : null,
            'model' => $this->role->value === 'assistant' ? data_get($this->metadata, 'model') : null,
            'confidence' => $this->role->value === 'assistant' ? data_get($this->metadata, 'confidence') : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
