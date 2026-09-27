<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->date('journal_date');
            $table->string('mood', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'journal_date']);
            $table->index(['user_id', 'mood']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_journals');
    }
};

