<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title', 160);
            $table->text('body');
            $table->json('data')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->string('deduplication_key', 190);
            $table->date('reference_date')->nullable();
            $table->timestampTz('available_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampTz('read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'deduplication_key']);
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type', 'created_at']);
            $table->index(['available_at', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
