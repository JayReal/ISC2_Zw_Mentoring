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
        Schema::create('match_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentoring_goal_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('goal_milestone_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('comment');
            $table->text('body');
            $table->timestamps();
            $table->index(['mentoring_match_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_activities');
    }
};
