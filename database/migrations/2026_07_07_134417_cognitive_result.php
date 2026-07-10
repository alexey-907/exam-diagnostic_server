<?php
// database/migrations/2026_07_07_000001_create_cognitive_results_table.php

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
        Schema::create('cognitive_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('session_id')
                ->constrained('student_sessions')
                ->onDelete('cascade');
            $table->string('test_code', 40);
            $table->decimal('raw_score', 6, 2);
            $table->integer('normalized_score');
            $table->string('level_code', 20);
            $table->text('interpretation')->nullable();
            $table->timestamps();

            // Уникальность: одна запись на сессию + тест
            $table->unique(['session_id', 'test_code']);

            // Индексы для быстрого поиска
            $table->index('session_id');
            $table->index('test_code');
            $table->index('level_code');
        });

        // Добавляем CHECK constraint для normalized_score (0-100)
        DB::statement('ALTER TABLE cognitive_results ADD CONSTRAINT chk_cognitive_normalized_score CHECK (normalized_score BETWEEN 0 AND 100)');

        // Добавляем CHECK constraint для level_code
        DB::statement("ALTER TABLE cognitive_results ADD CONSTRAINT chk_cognitive_level_code CHECK (level_code IN ('low', 'medium', 'high'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cognitive_results');
    }
};
