<?php

namespace App\Events;

use App\Models\UserNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly UserNotification $notification) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('users.'.$this->notification->user_id.'.notifications')];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastQueue(): string
    {
        return 'notifications';
    }

    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id' => $this->notification->id,
                'type' => $this->notification->type->value,
                'title' => $this->notification->title,
                'message' => $this->notification->body,
                'action_url' => $this->notification->action_url,
                'created_at' => $this->notification->created_at?->toISOString(),
            ],
        ];
    }
}
