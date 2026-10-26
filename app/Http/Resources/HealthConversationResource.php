<?php

namespace App\Http\Resources;

use App\Enums\HealthMessageRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'messages_count' => $this->whenCounted('messages'),
            'messages' => $this->when($this->relationLoaded('messages'), fn () => HealthMessageResource::collection(
                $this->messages->where('role', '!=', HealthMessageRole::System)->values()
            )),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
