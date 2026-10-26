<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutrition_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('summary_date');
            $table->unsignedSmallInteger('calorie_target');
            $table->decimal('calories_consumed', 9, 2)->default(0);
            $table->decimal('calories_remaining', 9, 2)->default(0);
            $table->unsignedSmallInteger('meals_logged')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'summary_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_daily_summaries');
    }
};
