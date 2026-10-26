<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\BmiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeaturePlaceholderController;
use App\Http\Controllers\FoodAnalysisController;
use App\Http\Controllers\HealthAssistantController;
use App\Http\Controllers\HealthAssistantMessageController;
use App\Http\Controllers\HealthGoalController;
use App\Http\Controllers\HealthInsightsController;
use App\Http\Controllers\HealthJournalController;
use App\Http\Controllers\HealthJournalEntryController;
use App\Http\Controllers\HealthPlanController;
use App\Http\Controllers\HealthPlanTaskController;
use App\Http\Controllers\HealthProfileController;
use App\Http\Controllers\HealthyMenuController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\MealItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\NutritionDashboardController;
use App\Http\Controllers\NutritionProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SymptomCheckerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::patch('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/features/{feature}', FeaturePlaceholderController::class)
        ->whereIn('feature', array_keys(config('healo.features')))
        ->name('features.show');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/settings/notifications', [NotificationPreferenceController::class, 'edit'])->name('notification-preferences.edit');
    Route::put('/settings/notifications', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');

    Route::get('/health-profile', [HealthProfileController::class, 'edit'])->name('health-profile.edit');
    Route::put('/health-profile', [HealthProfileController::class, 'update'])->name('health-profile.update');

    Route::get('/bmi', [BmiController::class, 'index'])->name('bmi.index');
    Route::post('/bmi', [BmiController::class, 'store'])->name('bmi.store');
    Route::delete('/bmi/{bmiRecord}', [BmiController::class, 'destroy'])->name('bmi.destroy');

    Route::resource('health-journals', HealthJournalController::class);
    Route::post('/health-journals/{healthJournal}/entries', [HealthJournalEntryController::class, 'store'])->name('health-journal-entries.store');
    Route::put('/health-journal-entries/{healthJournalEntry}', [HealthJournalEntryController::class, 'update'])->name('health-journal-entries.update');
    Route::delete('/health-journal-entries/{healthJournalEntry}', [HealthJournalEntryController::class, 'destroy'])->name('health-journal-entries.destroy');

    Route::get('/nutrition', NutritionDashboardController::class)->name('nutrition.index');
    Route::get('/nutrition/profile', [NutritionProfileController::class, 'edit'])->name('nutrition.profile.edit');
    Route::put('/nutrition/profile', [NutritionProfileController::class, 'update'])->name('nutrition.profile.update');
    Route::resource('meals', MealController::class);
    Route::post('/meals/{meal}/items', [MealItemController::class, 'store'])->name('meal-items.store');
    Route::put('/meal-items/{mealItem}', [MealItemController::class, 'update'])->name('meal-items.update');
    Route::delete('/meal-items/{mealItem}', [MealItemController::class, 'destroy'])->name('meal-items.destroy');

    Route::get('/food-analysis', [FoodAnalysisController::class, 'create'])->name('food-analysis.create');
    Route::post('/food-analysis', [FoodAnalysisController::class, 'store'])->middleware('throttle:food-analysis')->name('food-analysis.store');
    Route::get('/food-analysis/{foodAnalysis}', [FoodAnalysisController::class, 'show'])->name('food-analysis.show');
    Route::get('/food-analysis/{foodAnalysis}/image', [FoodAnalysisController::class, 'image'])->name('food-analysis.image');
    Route::post('/food-analysis/{foodAnalysis}/confirm', [FoodAnalysisController::class, 'confirm'])->name('food-analysis.confirm');
    Route::delete('/food-analysis/{foodAnalysis}', [FoodAnalysisController::class, 'destroy'])->name('food-analysis.destroy');

    Route::get('/healthy-menus', [HealthyMenuController::class, 'index'])->name('healthy-menus.index');

    Route::get('/health-insights', HealthInsightsController::class)->middleware('throttle:health-ai')->name('health-insights.index');
    Route::resource('health-goals', HealthGoalController::class)
        ->parameters(['health-goals' => 'goal'])->except(['show']);

    Route::get('/health-plan', [HealthPlanController::class, 'index'])->middleware('throttle:health-ai')->name('health-plan.index');
    Route::post('/health-plan/generate', [HealthPlanController::class, 'generate'])->name('health-plan.generate');
    Route::post('/health-plan/tasks/{task}/complete', [HealthPlanTaskController::class, 'complete'])->name('health-plan.tasks.complete');
    Route::post('/health-plan/tasks/{task}/uncomplete', [HealthPlanTaskController::class, 'uncomplete'])->name('health-plan.tasks.uncomplete');

    Route::get('/health-assistant', [HealthAssistantController::class, 'index'])->name('health-assistant.index');
    Route::post('/health-assistant/conversations', [HealthAssistantController::class, 'store'])->name('health-assistant.store');
    Route::get('/health-assistant/conversations/{conversation}', [HealthAssistantController::class, 'show'])->name('health-assistant.show');
    Route::delete('/health-assistant/conversations/{conversation}', [HealthAssistantController::class, 'destroy'])->name('health-assistant.destroy');
    Route::post('/health-assistant/conversations/{conversation}/messages', [HealthAssistantMessageController::class, 'store'])->middleware('throttle:health-ai')->name('health-assistant.messages.store');

    Route::get('/symptom-checker', [SymptomCheckerController::class, 'create'])->name('symptom-checker.create');
    Route::post('/symptom-checker', [SymptomCheckerController::class, 'store'])->middleware('throttle:health-ai')->name('symptom-checker.store');
    Route::get('/symptom-checker/history', [SymptomCheckerController::class, 'history'])->name('symptom-checker.history');
    Route::get('/symptom-checker/{assessment}', [SymptomCheckerController::class, 'show'])->name('symptom-checker.show');
    Route::post('/symptom-checker/{assessment}/journal', [SymptomCheckerController::class, 'saveToJournal'])->name('symptom-checker.journal.store');
    Route::delete('/symptom-checker/{assessment}', [SymptomCheckerController::class, 'destroy'])->name('symptom-checker.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
});

require __DIR__.'/auth.php';
