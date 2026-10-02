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
        Schema::table('mentoring_goals', function (Blueprint $table) {
            $table->timestamp('mentor_agreed_at')->nullable()->after('agreed_at');
            $table->timestamp('mentee_agreed_at')->nullable()->after('mentor_agreed_at');
        });
        Schema::table('goal_milestones', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goal_milestones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
        Schema::table('mentoring_goals', function (Blueprint $table) {
            $table->dropColumn(['mentor_agreed_at', 'mentee_agreed_at']);
        });
    }
};
