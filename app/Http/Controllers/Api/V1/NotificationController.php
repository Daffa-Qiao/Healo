<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\UserNotification;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationService $notifications): AnonymousResourceCollection
    {
        return NotificationResource::collection($notifications->paginate(
            $request->user(),
            $request->string('filter')->toString(),
            $request->integer('per_page', 20),
        ));
    }

    public function unreadCount(Request $request, NotificationService $notifications): JsonResponse
    {
        return response()->json(['data' => ['unread_count' => $notifications->unreadCount($request->user())]]);
    }

    public function read(Request $request, UserNotification $notification, NotificationService $notifications): NotificationResource
    {
        $this->authorize('update', $notification);

        return new NotificationResource($notifications->markAsRead($request->user(), $notification));
    }

    public function readAll(Request $request, NotificationService $notifications): JsonResponse
    {
        $notifications->markAllAsRead($request->user());

        return response()->json(['data' => ['unread_count' => 0]]);
    }
}
