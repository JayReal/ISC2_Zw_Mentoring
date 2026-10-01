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
        Schema::create('mentoring_charters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_match_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('meeting_cadence');
            $table->string('communication_method');
            $table->text('response_expectations');
            $table->text('cancellation_expectations');
            $table->text('confidentiality_boundaries');
            $table->text('escalation_route')->nullable();
            $table->date('review_on');
            $table->date('closure_on');
            $table->foreignId('last_updated_by')->constrained('users');
            $table->timestamp('mentor_confirmed_at')->nullable();
            $table->timestamp('mentee_confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentoring_charters');
    }
};
