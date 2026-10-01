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
        Schema::create('mentoring_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_cycle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cluster_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tier')->default('matched');
            $table->string('status')->default('proposed');
            $table->unsignedTinyInteger('compatibility_score')->nullable();
            $table->text('rationale');
            $table->text('override_reason')->nullable();
            $table->timestamp('mentor_confirmed_at')->nullable();
            $table->timestamp('mentee_confirmed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'programme_cycle_id']);
            $table->unique(['mentor_id', 'mentee_id', 'programme_cycle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentoring_matches');
    }
};
