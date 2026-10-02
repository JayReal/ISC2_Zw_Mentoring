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
        Schema::create('mentoring_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('meeting_on');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('topics_discussed');
            $table->text('decisions')->nullable();
            $table->text('next_actions')->nullable();
            $table->date('next_meeting_on')->nullable();
            $table->timestamps();
            $table->index(['mentoring_match_id', 'meeting_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentoring_meetings');
    }
};
