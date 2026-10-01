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
        Schema::table('mentoring_matches', function (Blueprint $table) {
            $table->foreignId('declined_by')->nullable()->after('mentee_confirmed_at')->constrained('users')->nullOnDelete();
            $table->text('decline_reason')->nullable()->after('declined_by');
            $table->foreignId('rematch_requested_by')->nullable()->after('decline_reason')->constrained('users')->nullOnDelete();
            $table->text('rematch_reason')->nullable()->after('rematch_requested_by');
            $table->timestamp('last_activity_at')->nullable()->after('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentoring_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('declined_by');
            $table->dropColumn('decline_reason');
            $table->dropConstrainedForeignId('rematch_requested_by');
            $table->dropColumn('rematch_reason');
            $table->dropColumn('last_activity_at');
        });
    }
};
