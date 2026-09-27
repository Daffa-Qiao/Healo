<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Persist only low-sensitivity operational context. Health content must
     * never be passed through the metadata argument.
     *
     * @param  array<string, scalar|null>  $metadata
     */
    public function record(
        ?User $actor,
        string $action,
        ?Model $subject = null,
        array $metadata = [],
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();

        return AuditLog::query()->create([
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'safe_metadata' => $metadata ?: null,
            'ip_hash' => $request->ip()
                ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
                : null,
            'user_agent' => $request->userAgent()
                ? mb_substr($request->userAgent(), 0, 255)
                : null,
        ]);
    }
}
