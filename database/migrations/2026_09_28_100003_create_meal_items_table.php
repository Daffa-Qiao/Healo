<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->constrained()->cascadeOnDelete();
            $table->string('food_name', 160);
            $table->decimal('serving_size', 8, 2);
            $table->string('serving_unit', 30);
            $table->decimal('calories', 9, 2);
            $table->decimal('protein_g', 8, 2)->default(0);
            $table->decimal('carbohydrates_g', 8, 2)->default(0);
            $table->decimal('fat_g', 8, 2)->default(0);
            $table->string('source', 20)->default('manual');
            $table->decimal('confidence', 4, 3)->nullable();
            $table->timestamps();

            $table->index(['meal_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_items');
    }
};
