<?php
// database/migrations/2026_07_01_085637_create_student_sessions_table.php
// (обновленная версия)

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
        Schema::create('student_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('roles')
                ->onDelete('cascade');
            $table->integer('grade');
            $table->string('region', 120)->nullable();
            $table->string('exam_type', 10)->nullable();
            $table->integer('target_year');
            $table->string('target_track', 30)->nullable();
            $table->boolean('consent_personal_data')->default(false);
            $table->string('status', 30)->default('started');
            $table->timestamps();

            // Индексы
            $table->index('user_id');
            $table->index('grade');
            $table->index('exam_type');
            $table->index('status');
            $table->index(['user_id', 'status']);
        });

        // Добавляем CHECK constraints
        DB::statement('ALTER TABLE student_sessions ADD CONSTRAINT chk_session_grade CHECK (grade BETWEEN 7 AND 11)');
        DB::statement("ALTER TABLE student_sessions ADD CONSTRAINT chk_session_exam_type CHECK (exam_type IN ('OGE', 'EGE', 'EARLY'))");
        DB::statement("ALTER TABLE student_sessions ADD CONSTRAINT chk_session_status CHECK (status IN ('started', 'testing', 'calculated', 'completed'))");
        DB::statement("ALTER TABLE student_sessions ADD CONSTRAINT chk_session_target_track CHECK (target_track IN ('UNIVERSITY', 'COLLEGE', 'PROFILE_CLASS', 'UNKNOWN'))");
        DB::statement('ALTER TABLE student_sessions ADD CONSTRAINT chk_session_target_year CHECK (target_year >= 2027)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_sessions');
    }
};
