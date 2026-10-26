<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('health_plan_enabled')->default(true);
            $table->boolean('meal_reminder_enabled')->default(true);
            $table->boolean('journal_reminder_enabled')->default(true);
            $table->boolean('health_goal_enabled')->default(true);
            $table->boolean('health_insight_enabled')->default(true);
            $table->boolean('wellness_enabled')->default(true);
            $table->boolean('system_enabled')->default(true);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->time('reminder_time')->default('08:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
