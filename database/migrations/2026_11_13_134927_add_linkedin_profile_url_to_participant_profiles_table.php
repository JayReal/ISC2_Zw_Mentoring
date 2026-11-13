<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participant_profiles', function (Blueprint $table): void {
            $table->string('linkedin_profile_url', 500)->nullable()->after('experience_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_profiles', function (Blueprint $table): void {
            $table->dropColumn('linkedin_profile_url');
        });
    }
};
