<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_plan_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('health_plan_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title', 160);
            $table->string('description', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('priority')->default(50);
            $table->string('source', 30);
            $table->string('source_reference', 100);
            $table->string('completion_mode', 20)->default('manual');
            $table->timestampTz('completed_at')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->unique(['health_plan_id', 'type', 'source_reference'], 'health_plan_task_identity');
            $table->index(['health_plan_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_plan_tasks');
    }
};
