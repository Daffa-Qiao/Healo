<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_journals', function (Blueprint $table) {
            $table->decimal('weight_kg', 5, 2)->nullable()->after('mood');
            $table->unsignedSmallInteger('systolic')->nullable()->after('weight_kg');
            $table->unsignedSmallInteger('diastolic')->nullable()->after('systolic');
            $table->decimal('sleep_hours', 4, 2)->nullable()->after('diastolic');
            $table->decimal('water_liters', 4, 2)->nullable()->after('sleep_hours');
            $table->unsignedSmallInteger('exercise_minutes')->nullable()->after('water_liters');
        });
    }

    public function down(): void
    {
        Schema::table('health_journals', function (Blueprint $table) {
            $table->dropColumn([
                'weight_kg',
                'systolic',
                'diastolic',
                'sleep_hours',
                'water_liters',
                'exercise_minutes',
            ]);
        });
    }
};
