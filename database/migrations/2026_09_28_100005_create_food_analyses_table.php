<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('image_path')->nullable();
            $table->string('status', 20);
            $table->text('result')->nullable();
            $table->decimal('confidence', 4, 3)->nullable();
            $table->string('provider', 50)->nullable();
            $table->text('failure_code')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_analyses');
    }
};
