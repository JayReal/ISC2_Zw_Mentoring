<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_profiles', function (Blueprint $table): void {
            $table->json('mentoring_styles')->nullable()->after('preferred_format');
        });
    }

    public function down(): void
    {
        Schema::table('participant_profiles', function (Blueprint $table): void {
            $table->dropColumn('mentoring_styles');
        });
    }
};
