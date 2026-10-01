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
        if (! Schema::hasTable('mentoring_check_ins')) {
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
                $table->unique(['mentoring_match_id', 'user_id', 'period_month'], 'checkins_match_user_month_unique');
            });

            return;
        }

        // MySQL can leave the table behind when CREATE TABLE succeeds but the
        // automatically named unique index exceeds its 64-character limit.
        Schema::table('mentoring_check_ins', function (Blueprint $table) {
            $table->unique(['mentoring_match_id', 'user_id', 'period_month'], 'checkins_match_user_month_unique');
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
