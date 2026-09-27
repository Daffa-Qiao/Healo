<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::query()->where('role', UserRole::User)->count(),
            'newUsers' => User::query()->where('role', UserRole::User)->where('created_at', '>=', now()->subDays(30))->count(),
            'auditEvents' => AuditLog::query()->where('created_at', '>=', now()->subDay())->count(),
        ]);
    }
}
