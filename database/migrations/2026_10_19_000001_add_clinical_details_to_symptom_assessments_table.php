<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('symptom_assessments', function (Blueprint $table) {
            $table->text('differential')->nullable()->after('possible_categories');
            $table->text('recommended_specialty')->nullable()->after('differential');
            $table->boolean('referral_needed')->default(false)->after('recommended_specialty');
        });
    }

    public function down(): void
    {
        Schema::table('symptom_assessments', function (Blueprint $table) {
            $table->dropColumn(['differential', 'recommended_specialty', 'referral_needed']);
        });
    }
};
