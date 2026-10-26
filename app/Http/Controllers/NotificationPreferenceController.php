<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNotificationPreferenceRequest;
use App\Services\Notification\NotificationPreferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    public function edit(Request $request, NotificationPreferenceService $preferences): View
    {
        return view('notifications.preferences', [
            'preference' => $preferences->forUser($request->user()),
        ]);
    }

    public function update(UpdateNotificationPreferenceRequest $request, NotificationPreferenceService $preferences): RedirectResponse
    {
        $preferences->update($request->user(), $request->validated());

        return redirect()->route('notification-preferences.edit')->with('status', 'notification-preferences-saved');
    }
}
