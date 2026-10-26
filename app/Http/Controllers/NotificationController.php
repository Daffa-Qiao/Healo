<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationService $notifications): View
    {
        $filter = $request->string('filter')->toString();

        return view('notifications.index', [
            'notifications' => $notifications->paginate($request->user(), $filter),
            'unreadCount' => $notifications->unreadCount($request->user()),
            'activeFilter' => $filter ?: 'all',
        ]);
    }

    public function read(Request $request, UserNotification $notification, NotificationService $notifications): RedirectResponse
    {
        $this->authorize('update', $notification);
        $notifications->markAsRead($request->user(), $notification);

        return $notification->action_url
            ? redirect()->to($notification->action_url)
            : back()->with('status', 'notification-read');
    }

    public function readAll(Request $request, NotificationService $notifications): RedirectResponse
    {
        $notifications->markAllAsRead($request->user());

        return back()->with('status', 'notifications-read-all');
    }
}
