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
        Schema::table('participant_profiles', function (Blueprint $table) {
            $table->text('mentor_expertise')->nullable();
            $table->text('mentor_prerequisites')->nullable();
            $table->unsignedTinyInteger('mentor_capacity')->default(1);
            $table->string('mentor_availability_status')->default('available');
            $table->timestamp('mentor_orientation_completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_profiles', function (Blueprint $table) {
            $table->dropColumn(['mentor_expertise', 'mentor_prerequisites', 'mentor_capacity', 'mentor_availability_status', 'mentor_orientation_completed_at']);
        });
    }
};
