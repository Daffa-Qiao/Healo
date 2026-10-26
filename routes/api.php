<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BmiRecordController;
use App\Http\Controllers\Api\V1\FoodAnalysisController;
use App\Http\Controllers\Api\V1\HealthAssistantMessageController;
use App\Http\Controllers\Api\V1\HealthConversationController;
use App\Http\Controllers\Api\V1\HealthGoalController;
use App\Http\Controllers\Api\V1\HealthInsightsController;
use App\Http\Controllers\Api\V1\HealthJournalController;
use App\Http\Controllers\Api\V1\HealthJournalEntryController;
use App\Http\Controllers\Api\V1\HealthPlanController;
use App\Http\Controllers\Api\V1\HealthPlanTaskController;
use App\Http\Controllers\Api\V1\HealthProfileController;
use App\Http\Controllers\Api\V1\HealthyMenuController;
use App\Http\Controllers\Api\V1\MealController;
use App\Http\Controllers\Api\V1\MealItemController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\NutritionProfileController;
use App\Http\Controllers\Api\V1\NutritionSummaryController;
use App\Http\Controllers\Api\V1\SymptomAssessmentController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    });

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::delete('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/profile', fn (Request $request) => new UserResource($request->user()))->name('profile.show');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::get('/notification-preferences', [NotificationPreferenceController::class, 'show'])->name('notification-preferences.show');
        Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');

        Route::get('/health-profile', [HealthProfileController::class, 'show'])->name('health-profile.show');
        Route::put('/health-profile', [HealthProfileController::class, 'update'])->name('health-profile.update');

        Route::apiResource('bmi-records', BmiRecordController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::apiResource('health-journals', HealthJournalController::class);
        Route::post('/health-journals/{healthJournal}/entries', [HealthJournalEntryController::class, 'store'])->name('health-journal-entries.store');
        Route::put('/health-journal-entries/{healthJournalEntry}', [HealthJournalEntryController::class, 'update'])->name('health-journal-entries.update');
        Route::delete('/health-journal-entries/{healthJournalEntry}', [HealthJournalEntryController::class, 'destroy'])->name('health-journal-entries.destroy');

        Route::get('/nutrition/profile', [NutritionProfileController::class, 'show'])->name('nutrition.profile.show');
        Route::put('/nutrition/profile', [NutritionProfileController::class, 'update'])->name('nutrition.profile.update');
        Route::get('/nutrition/today', [NutritionSummaryController::class, 'today'])->name('nutrition.today');
        Route::get('/nutrition/summary', [NutritionSummaryController::class, 'weekly'])->name('nutrition.summary');
        Route::apiResource('meals', MealController::class);
        Route::post('/meals/{meal}/items', [MealItemController::class, 'store'])->name('meal-items.store');
        Route::put('/meal-items/{mealItem}', [MealItemController::class, 'update'])->name('meal-items.update');
        Route::delete('/meal-items/{mealItem}', [MealItemController::class, 'destroy'])->name('meal-items.destroy');
        Route::post('/nutrition/food-analysis', [FoodAnalysisController::class, 'store'])->middleware('throttle:food-analysis')->name('nutrition.food-analysis.store');
        Route::get('/nutrition/food-analysis/{foodAnalysis}', [FoodAnalysisController::class, 'show'])->name('nutrition.food-analysis.show');
        Route::post('/nutrition/food-analysis/{foodAnalysis}/confirm', [FoodAnalysisController::class, 'confirm'])->name('nutrition.food-analysis.confirm');
        Route::delete('/nutrition/food-analysis/{foodAnalysis}', [FoodAnalysisController::class, 'destroy'])->name('nutrition.food-analysis.destroy');
        Route::get('/nutrition/menus', [HealthyMenuController::class, 'index'])->name('nutrition.menus.index');

        Route::get('/health-insights', [HealthInsightsController::class, 'index'])->middleware('throttle:health-ai')->name('health-insights.index');
        Route::get('/health-insights/trends', [HealthInsightsController::class, 'trends'])->name('health-insights.trends');
        Route::get('/health-insights/weekly', [HealthInsightsController::class, 'weekly'])->name('health-insights.weekly');
        Route::apiResource('health-goals', HealthGoalController::class)
            ->parameters(['health-goals' => 'goal']);

        Route::get('/health-plan', [HealthPlanController::class, 'index'])->name('health-plan.index');
        Route::get('/health-plan/today', [HealthPlanController::class, 'today'])->middleware('throttle:health-ai')->name('health-plan.today');
        Route::post('/health-plan/generate', [HealthPlanController::class, 'generate'])->name('health-plan.generate');
        Route::post('/health-plan/tasks/{task}/complete', [HealthPlanTaskController::class, 'complete'])->name('health-plan.tasks.complete');
        Route::post('/health-plan/tasks/{task}/uncomplete', [HealthPlanTaskController::class, 'uncomplete'])->name('health-plan.tasks.uncomplete');

        Route::get('/health-assistant/conversations', [HealthConversationController::class, 'index'])->name('health-assistant.conversations.index');
        Route::post('/health-assistant/conversations', [HealthConversationController::class, 'store'])->name('health-assistant.conversations.store');
        Route::get('/health-assistant/conversations/{conversation}', [HealthConversationController::class, 'show'])->name('health-assistant.conversations.show');
        Route::post('/health-assistant/conversations/{conversation}/messages', [HealthAssistantMessageController::class, 'store'])->middleware('throttle:health-ai')->name('health-assistant.messages.store');
        Route::delete('/health-assistant/conversations/{conversation}', [HealthConversationController::class, 'destroy'])->name('health-assistant.conversations.destroy');

        Route::post('/symptom-checker/assess', [SymptomAssessmentController::class, 'store'])->middleware('throttle:health-ai')->name('symptom-checker.assess');
        Route::get('/symptom-checker/history', [SymptomAssessmentController::class, 'index'])->name('symptom-checker.history');
        Route::get('/symptom-checker/{assessment}', [SymptomAssessmentController::class, 'show'])->name('symptom-checker.show');
        Route::post('/symptom-checker/{assessment}/journal', [SymptomAssessmentController::class, 'saveToJournal'])->name('symptom-checker.journal.store');
        Route::delete('/symptom-checker/{assessment}', [SymptomAssessmentController::class, 'destroy'])->name('symptom-checker.destroy');
    });
});
