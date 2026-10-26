<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->decimal('target_value', 10, 2);
            $table->decimal('current_value', 10, 2)->nullable();
            $table->string('unit', 30);
            $table->date('start_date');
            $table->date('target_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_goals');
    }
};
