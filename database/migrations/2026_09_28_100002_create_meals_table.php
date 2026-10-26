<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('meal_date');
            $table->string('meal_type', 20);
            $table->text('notes')->nullable();
            $table->decimal('total_calories', 9, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'meal_date', 'meal_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
