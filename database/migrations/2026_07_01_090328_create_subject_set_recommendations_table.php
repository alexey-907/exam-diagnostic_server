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
        Schema::create('subject_set_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('student_sessions')->onDelete('cascade');
            $table->integer('rank');
            $table->jsonb('subject_codes');
            $table->integer('score_total');
            $table->string('risk_level', 20);
            $table->jsonb('explanation_json');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_set_recommendations');
    }
};
