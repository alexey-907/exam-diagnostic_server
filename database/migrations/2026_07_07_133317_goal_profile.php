<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('session_id')->constrained('student_sessions')->onDelete('cascade');
            $table->string('goal_type', 30)->default('UNKNOWN');
            $table->string('target_profession', 255)->nullable();
            $table->string('target_program', 255)->nullable();
            $table->string('target_city', 120)->nullable();
            $table->string('priority_type', 30)->default('balanced');
            $table->timestamps();

            $table->index('session_id');
            $table->unique('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_profiles');
    }
};
