<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutrition_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('daily_calorie_target');
            $table->string('activity_level', 30);
            $table->string('goal', 30);
            $table->string('diet_preference', 30)->default('balanced');
            $table->text('food_preferences')->nullable();
            $table->string('target_source', 20)->default('calculated');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_profiles');
    }
};
