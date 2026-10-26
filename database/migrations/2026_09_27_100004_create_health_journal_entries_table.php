<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_journal_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->text('content');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['health_journal_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_journal_entries');
    }
};
