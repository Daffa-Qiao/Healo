<?php

namespace App\Services\Notification;

use App\Enums\NotificationType;
use App\Events\NotificationCreated;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NotificationService
{
    public function __construct(private readonly NotificationPreferenceService $preferences) {}

    /** @param array<string, scalar|null> $data */
    public function create(
        User $user,
        NotificationType $type,
        string $title,
        string $body,
        array $data = [],
        ?string $actionUrl = null,
        ?CarbonInterface $referenceDate = null,
        ?string $referenceKey = null,
    ): ?UserNotification {
        $preference = $this->preferences->forUser($user);
        if (! $preference->notifications_enabled || ! $preference->{$type->preferenceColumn()}) {
            return null;
        }

        $date = $referenceDate?->toDateString();
        $identity = implode(':', array_filter([$type->value, $date, $referenceKey ?: Str::slug($title)]));
        $availableAt = $this->availableAt($preference->quiet_hours_start, $preference->quiet_hours_end);

        try {
            $notification = $user->userNotifications()->firstOrCreate(
                ['deduplication_key' => $identity],
                [
                    'type' => $type,
                    'title' => Str::limit(strip_tags($title), 160, ''),
                    'body' => strip_tags($body),
                    'data' => $data,
                    'action_url' => $this->safeActionUrl($actionUrl),
                    'reference_date' => $date,
                    'available_at' => $availableAt,
                ],
            );
        } catch (QueryException $exception) {
            $notification = $user->userNotifications()->where('deduplication_key', $identity)->first();
            if (! $notification) {
                throw $exception;
            }
        }

        if ($notification->wasRecentlyCreated && $availableAt === null) {
            $this->broadcast($notification);
        }

        return $notification;
    }

    public function markAsRead(User $user, UserNotification $notification): UserNotification
    {
        $this->ensureOwner($user, $notification);
        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return $notification->refresh();
    }

    public function markAllAsRead(User $user): int
    {
        return $user->userNotifications()->unread()->update(['read_at' => now(), 'updated_at' => now()]);
    }

    public function delete(User $user, UserNotification $notification): void
    {
        $this->ensureOwner($user, $notification);
        $notification->delete();
    }

    public function unreadCount(User $user): int
    {
        return $user->userNotifications()->unread()->count();
    }

    /** @return EloquentCollection<int, UserNotification> */
    public function latest(User $user, int $limit = 5): EloquentCollection
    {
        return $user->userNotifications()->latest()->limit(min(10, max(1, $limit)))->get();
    }

    public function deliverDue(int $limit = 500): int
    {
        $delivered = 0;
        UserNotification::query()
            ->whereNull('delivered_at')
            ->whereNotNull('available_at')
            ->where('available_at', '<=', now())
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (UserNotification $notification) use (&$delivered): void {
                if (UserNotification::query()->whereKey($notification->id)->whereNull('delivered_at')->update(['delivered_at' => now(), 'updated_at' => now()]) === 1) {
                    event(new NotificationCreated($notification->refresh()));
                    $delivered++;
                }
            });

        return $delivered;
    }

    public function paginate(User $user, ?string $filter = null, int $perPage = 20): LengthAwarePaginator
    {
        return $user->userNotifications()
            ->when($filter === 'unread', fn ($query) => $query->unread())
            ->when(in_array($filter, ['health', 'wellness', 'system'], true), function ($query) use ($filter): void {
                $types = collect(NotificationType::cases())->filter(fn (NotificationType $type) => $type->group() === $filter)->map->value;
                $query->whereIn('type', $types);
            })
            ->latest()
            ->paginate(min(50, max(1, $perPage)));
    }

    private function ensureOwner(User $user, UserNotification $notification): void
    {
        if ($notification->user_id !== $user->id) {
            throw new AuthorizationException;
        }
    }

    private function broadcast(UserNotification $notification): void
    {
        if (UserNotification::query()->whereKey($notification->id)->whereNull('delivered_at')->update(['delivered_at' => now(), 'updated_at' => now()]) === 1) {
            event(new NotificationCreated($notification->refresh()));
        }
    }

    private function availableAt(?string $start, ?string $end): ?Carbon
    {
        if (! $start || ! $end) {
            return null;
        }

        $now = now();
        $startAt = $now->copy()->setTimeFromTimeString($start);
        $endAt = $now->copy()->setTimeFromTimeString($end);

        if ($startAt->equalTo($endAt)) {
            return null;
        }

        if ($startAt->lessThan($endAt)) {
            return $now->betweenIncluded($startAt, $endAt) ? $endAt : null;
        }

        if ($now->greaterThanOrEqualTo($startAt)) {
            return $endAt->addDay();
        }

        return $now->lessThan($endAt) ? $endAt : null;
    }

    private function safeActionUrl(?string $url): ?string
    {
        if ($url === null || ! str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return null;
        }

        return Str::limit($url, 500, '');
    }
}
