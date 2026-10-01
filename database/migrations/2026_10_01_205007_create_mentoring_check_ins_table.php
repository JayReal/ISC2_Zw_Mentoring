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
        Schema::create('mentoring_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('meeting_held');
            $table->unsignedTinyInteger('usefulness_score');
            $table->boolean('needs_support')->default(false);
            $table->text('comment')->nullable();
            $table->date('period_month');
            $table->timestamps();
            $table->unique(['mentoring_match_id', 'user_id', 'period_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentoring_check_ins');
    }
};
