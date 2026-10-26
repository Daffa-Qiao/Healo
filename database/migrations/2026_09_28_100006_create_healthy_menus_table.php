<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('healthy_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('meal_type', 20);
            $table->string('diet_preference', 30)->default('balanced');
            $table->decimal('calories', 8, 2);
            $table->decimal('protein_g', 8, 2)->default(0);
            $table->decimal('carbohydrates_g', 8, 2)->default(0);
            $table->decimal('fat_g', 8, 2)->default(0);
            $table->json('ingredients');
            $table->text('preparation');
            $table->string('source', 100);
            $table->string('attribution', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'meal_type', 'calories']);
            $table->index(['active', 'diet_preference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('healthy_menus');
    }
};
