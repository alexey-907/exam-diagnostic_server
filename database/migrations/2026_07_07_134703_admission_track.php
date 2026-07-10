<?php
// database/migrations/2026_07_07_000002_create_admission_tracks_table.php

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
        Schema::create('admission_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('level', 10);
            $table->string('direction_code', 30)->nullable();
            $table->string('direction_title', 255);
            $table->jsonb('subject_set_rules');
            $table->text('source_url')->nullable();
            $table->timestamp('source_checked_at')->nullable();
            $table->integer('valid_year');
            $table->timestamps();

            // Индексы
            $table->index('level');
            $table->index('valid_year');
            $table->index('direction_code');

            // Составной индекс для частых запросов
            $table->index(['level', 'valid_year']);
        });

        // Добавляем CHECK constraint для level
        DB::statement("ALTER TABLE admission_tracks ADD CONSTRAINT chk_admission_level CHECK (level IN ('SPO', 'VO'))");

        // Добавляем CHECK constraint для valid_year
        DB::statement('ALTER TABLE admission_tracks ADD CONSTRAINT chk_admission_valid_year CHECK (valid_year >= 2024)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_tracks');
    }
};
