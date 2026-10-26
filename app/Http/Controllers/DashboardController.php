<?php

namespace App\Http\Controllers;

use App\Services\Insights\DashboardHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardHealthService $dashboard): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard', $dashboard->forUser($request->user()));
    }
}
