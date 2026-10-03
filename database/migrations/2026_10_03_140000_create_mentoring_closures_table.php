<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoring_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_match_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by')->constrained('users');
            $table->foreignId('last_updated_by')->constrained('users');
            $table->string('reason');
            $table->text('summary');
            $table->text('next_steps')->nullable();
            $table->timestamp('mentor_confirmed_at')->nullable();
            $table->timestamp('mentee_confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_closures');
    }
};
