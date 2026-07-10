<?php
// database/migrations/2026_07_07_000004_create_subject_attitudes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subject_attitudes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('session_id')
                ->constrained('student_sessions')
                ->onDelete('cascade');
            $table->string('subject_code', 30);
            $table->string('question_code', 20);
            $table->integer('answer_code');
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            // Уникальность: одна запись на сессию + предмет + вопрос
            $table->unique(['session_id', 'subject_code', 'question_code'], 'unique_subject_attitude');

            // Индексы
            $table->index('session_id');
            $table->index('subject_code');
            $table->index('question_code');
        });

        // Добавляем CHECK constraint для answer_code (1-5)
        DB::statement('ALTER TABLE subject_attitudes ADD CONSTRAINT chk_answer_code CHECK (answer_code BETWEEN 1 AND 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_attitudes');
    }
};
