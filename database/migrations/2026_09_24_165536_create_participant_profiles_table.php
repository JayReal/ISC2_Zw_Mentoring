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
        Schema::create('participant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('programme_cycle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('primary_cluster_id')->nullable()->constrained('clusters')->nullOnDelete();
            $table->date('date_of_birth');
            $table->string('participation_type')->default('mentee');
            $table->string('pathway')->nullable();
            $table->text('goals')->nullable();
            $table->text('experience_summary')->nullable();
            $table->json('secondary_cluster_ids')->nullable();
            $table->json('availability')->nullable();
            $table->string('preferred_language')->default('English');
            $table->string('preferred_format')->default('virtual');
            $table->string('mentoring_style')->nullable();
            $table->string('university_context')->nullable();
            $table->text('accessibility_needs')->nullable();
            $table->text('conflict_declarations')->nullable();
            $table->string('intake_status')->default('not_started');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['programme_cycle_id', 'intake_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_profiles');
    }
};
