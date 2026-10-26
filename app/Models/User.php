<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $attributes = [
        'role' => 'user',
        'locale' => 'id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function healthProfile(): HasOne
    {
        return $this->hasOne(HealthProfile::class);
    }

    public function bmiRecords(): HasMany
    {
        return $this->hasMany(BmiRecord::class);
    }

    public function healthJournals(): HasMany
    {
        return $this->hasMany(HealthJournal::class);
    }

    public function healthJournalEntries(): HasManyThrough
    {
        return $this->hasManyThrough(HealthJournalEntry::class, HealthJournal::class);
    }

    public function nutritionProfile(): HasOne
    {
        return $this->hasOne(NutritionProfile::class);
    }

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }

    public function mealItems(): HasManyThrough
    {
        return $this->hasManyThrough(MealItem::class, Meal::class);
    }

    public function nutritionDailySummaries(): HasMany
    {
        return $this->hasMany(NutritionDailySummary::class);
    }

    public function foodAnalyses(): HasMany
    {
        return $this->hasMany(FoodAnalysis::class);
    }

    public function healthConversations(): HasMany
    {
        return $this->hasMany(HealthConversation::class);
    }

    public function symptomAssessments(): HasMany
    {
        return $this->hasMany(SymptomAssessment::class);
    }

    public function healthGoals(): HasMany
    {
        return $this->hasMany(HealthGoal::class);
    }

    public function healthPlans(): HasMany
    {
        return $this->hasMany(HealthPlan::class);
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }
}
