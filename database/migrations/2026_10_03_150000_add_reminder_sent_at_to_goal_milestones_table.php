<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goal_milestones', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('due_on');
            $table->index(['status', 'due_on', 'reminder_sent_at'], 'milestones_due_reminder_index');
        });
    }

    public function down(): void
    {
        Schema::table('goal_milestones', function (Blueprint $table) {
            $table->dropIndex('milestones_due_reminder_index');
            $table->dropColumn('reminder_sent_at');
        });
    }
};
