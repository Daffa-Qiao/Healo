<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptom_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('health_journal_id')->nullable()->constrained()->nullOnDelete();
            $table->text('input');
            $table->text('summary');
            $table->string('urgency', 20);
            $table->text('red_flags');
            $table->text('symptoms');
            $table->text('possible_categories');
            $table->text('recommendation');
            $table->string('confidence', 20);
            $table->string('provider', 80);
            $table->string('model', 120);
            $table->timestamp('saved_to_journal_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'urgency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptom_assessments');
    }
};
