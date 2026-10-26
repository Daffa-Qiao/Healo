<?php

namespace App\Http\Controllers;

use App\Enums\FoodAnalysisStatus;
use App\Enums\MealType;
use App\Exceptions\FoodAnalysisException;
use App\Http\Requests\FoodAnalysisConfirmationRequest;
use App\Http\Requests\FoodAnalysisRequest;
use App\Models\FoodAnalysis;
use App\Services\Audit\AuditLogService;
use App\Services\Nutrition\FoodAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FoodAnalysisController extends Controller
{
    public function create(Request $request): View
    {
        $recent = $request->user()->foodAnalyses()
            ->where('status', FoodAnalysisStatus::Review)
            ->latest()->limit(5)->get();

        return view('food-analysis.create', ['recentAnalyses' => $recent]);
    }

    public function store(FoodAnalysisRequest $request, FoodAnalysisService $service, AuditLogService $audit): RedirectResponse
    {
        try {
            $analysis = $service->analyze($request->user(), $request->file('image'));
        } catch (FoodAnalysisException) {
            return back()->withErrors(['image' => __('healo.food_analysis_failed')]);
        }

        $audit->record($request->user(), 'food_analysis.created', $analysis, request: $request);

        return redirect()->route('food-analysis.show', $analysis);
    }

    public function show(Request $request, int $foodAnalysis): View
    {
        $analysis = $this->ownedAnalysis($request, $foodAnalysis);
        $this->authorize('view', $analysis);

        return view('food-analysis.show', [
            'analysis' => $analysis,
            'mealTypes' => MealType::cases(),
        ]);
    }

    public function image(Request $request, int $foodAnalysis): StreamedResponse
    {
        $analysis = $this->ownedAnalysis($request, $foodAnalysis);
        $this->authorize('view', $analysis);
        abort_unless($analysis->image_path, 404);

        return Storage::disk((string) config('nutrition.image_disk', 'local'))
            ->response($analysis->image_path, 'food-photo', ['Cache-Control' => 'private, no-store']);
    }

    public function confirm(
        FoodAnalysisConfirmationRequest $request,
        int $foodAnalysis,
        FoodAnalysisService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $analysis = $this->ownedAnalysis($request, $foodAnalysis);
        $this->authorize('update', $analysis);

        try {
            $meal = $service->confirm($analysis, $request->validated());
        } catch (FoodAnalysisException) {
            return back()->withErrors(['foods' => __('healo.food_analysis_not_reviewable')]);
        }

        $audit->record($request->user(), 'food_analysis.confirmed', $analysis, request: $request);

        return redirect()->route('meals.show', $meal)->with('status', 'food-analysis-confirmed');
    }

    public function destroy(
        Request $request,
        int $foodAnalysis,
        FoodAnalysisService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $analysis = $this->ownedAnalysis($request, $foodAnalysis);
        $this->authorize('delete', $analysis);
        $audit->record($request->user(), 'food_analysis.cancelled', $analysis, request: $request);
        $service->cancel($analysis);

        return redirect()->route('food-analysis.create')->with('status', 'food-analysis-cancelled');
    }

    private function ownedAnalysis(Request $request, int $id): FoodAnalysis
    {
        return $request->user()->foodAnalyses()->findOrFail($id);
    }
}
