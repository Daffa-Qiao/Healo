<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->string('message_type', 30)->default('chat');
            $table->text('content');
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->index(['health_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_messages');
    }
};
