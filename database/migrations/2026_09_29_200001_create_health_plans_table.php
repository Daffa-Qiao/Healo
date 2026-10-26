<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('plan_date');
            $table->string('status', 20)->default('active');
            $table->unsignedSmallInteger('progress_percentage')->default(0);
            $table->timestampTz('generated_at');
            $table->timestampTz('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plan_date']);
            $table->index(['user_id', 'status']);
            $table->index('plan_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_plans');
    }
};
